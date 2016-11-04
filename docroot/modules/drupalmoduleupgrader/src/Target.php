<?php

namespace Drupal\drupalmoduleupgrader;

use Doctrine\Common\Collections\ArrayCollection;
use Drupal\Component\Utility\SafeMarkup;
use Pharborist\Node;
use Pharborist\Parser;
use Pharborist\RootNode;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

/**
 * Default implementation of TargetInterface.
 */
class Target implements TargetInterface {

  /**
   * The target module's machine name.
   *
   * @var string
   */
  protected $id;

  /**
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $indexerManager;

  /**
   * The target module's base path.
   *
   * @var string
   */
  protected $basePath;

  /**
   * @var IndexerInterface[]
   */
  protected $indexers = [];

  /**
   * @var \Doctrine\Common\Collections\ArrayCollection
   */
  protected $services;

  /**
   * All open documents.
   *
   * @var \Pharborist\RootNode[]
   */
  protected $documents = [];

  /**
   * Constructs a Target.
   *
   * @param string $path
   *  The base path of the target module.
   * @param ContainerInterface $container
   *  The current container, to pull any dependencies out of.
   */
  public function __construct($path, ContainerInterface $container) {
    $this->indexerManager = $container->get('plugin.manager.drupalmoduleupgrader.indexer');

    if (is_dir($path)) {
      $this->basePath = $path;
    }
    else {
      throw new \RuntimeException(SafeMarkup::format('Invalid base path: @path', ['@path' => $path]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    if (empty($this->id)) {
      $dir = $this->getBasePath();
      $info = (new Finder)->in($dir)->depth('== 0')->name('*.info')->getIterator();
      $info->rewind();

      if ($info_file = $info->current()) {
        $this->id = subStr($info_file->getFilename(), 0, -5);
      }
      else {
        throw new \RuntimeException(SafeMarkup::format('Could not find info file in @dir', ['@dir' => $dir]));
      }
    }
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getBasePath() {
    return $this->basePath;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath($file) {
    if ($file{0} == '.') {
      $file = $this->id() . $file;
    }
    return $this->getBasePath() . '/' . ltrim($file, '/');
  }

  /**
   * {@inheritdoc}
   */
  public function getFinder() {
      // We do NOT want to include submodules. We can detect one by the presence
      // of an info file -- if there is one, its directory is a submodule.
      $directories = (new Finder)
        ->directories()
        ->in($this->getBasePath())
        ->filter(function(\SplFileInfo $dir) {
          return (new Finder)->files()->in($dir->getPathname())->depth('== 0')->name('*.info')->count() === 0;
        });

      $directories = array_keys(iterator_to_array($directories));
      $directories[] = $this->getBasePath();

      return (new Finder)
        ->files()
        ->in($directories)
        // We don't need to recurse, because we've already determined which
        // directories to search.
        ->depth('== 0')
        ->name('*.module')
        ->name('*.install')
        ->name('*.inc')
        ->name('*.php')
        ->name('*.test');
  }

  /**
   * {@inheritdoc}
   */
  public function getIndexer($which) {
    if (empty($this->indexers[$which])) {
      /** @var IndexerInterface $indexer */
      $indexer = $this->indexerManager->createInstance($which);
      $indexer->bind($this);
      $this->indexers[$which] = $indexer;
    }
    return $this->indexers[$which];
  }

  /**
   * {@inheritdoc}
   */
  public function getServices() {
    if (empty($this->services)) {
      $this->services = new ArrayCollection();
    }
    return $this->services;
  }

  /**
   * Runs all available indexers on this target.
   */
  public function buildIndex() {
    $indexers = array_keys($this->indexerManager->getDefinitions());
    foreach ($indexers as $id) {
      $this->getIndexer($id)->build();
    }
    // Release syntax trees that were opened during indexing.
    $this->flush();
  }

  /**
   * Destroys all index data for this target.
   */
  public function destroyIndex() {
    $indexers = array_keys($this->indexerManager->getDefinitions());
    foreach ($indexers as $id) {
      $this->getIndexer($id)->destroy();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function implementsHook($hook) {
    return $this->getIndexer('function')->has('hook_' . $hook);
  }

  /**
   * {@inheritdoc}
   */
  public function executeHook($hook, array $arguments = []) {
    if ($this->implementsHook($hook)) {
      return $this->getIndexer('function')->execute('hook_' . $hook, $arguments);
    }
    else {
      $variables = [
        '@module' => $this->id(),
        '@hook' => $hook,
      ];
      throw new \InvalidArgumentException(SafeMarkup::format('@module does not implement hook_@hook.', $variables));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function open($file) {
    if (empty($this->documents[$file])) {
      $this->documents[$file] = Parser::parseFile($file);
    }
    return $this->documents[$file];
  }

  /**
   * {@inheritdoc}
   */
  public function save(Node $node = NULL) {
    if ($node) {
      $file = $this->getFileOf($node);
      if ($file) {
        $doc = $node instanceof RootNode ? $node : $node->parents()->get(0);

        $victory = file_put_contents($file, $doc->getText());
        if ($victory === FALSE) {
          throw new IOException(SafeMarkup::format('Failed to save @file.', [ '@file' => $file ]));
        }
      }
      else {
        throw new IOException('Cannot save a node that is not attached to an open document.');
      }
    }
    else {
      array_walk($this->documents, [ $this, 'save' ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function create($file, $ns = NULL) {
    $this->documents[$file] = RootNode::create($ns);
    return $this->documents[$file];
  }

  /**
   * {@inheritdoc}
   */
  public function flush() {
    $this->documents = [];
  }

  /**
   * Determines which currently-open file a node belongs to, if any. Nodes
   * which are not part of any open syntax tree will return NULL.
   *
   * @return string|NULL
   */
  public function getFileOf(Node $node) {
    if ($node instanceof RootNode) {
      $root = $node;
    }
    else {
      $parents = $node->parents();
      if ($parents->isEmpty()) {
        return NULL;
      }
      $root = $parents->get(0);
    }

    foreach ($this->documents as $file => $doc) {
      if ($root === $doc) {
        return $file;
      }
    }
  }

}
