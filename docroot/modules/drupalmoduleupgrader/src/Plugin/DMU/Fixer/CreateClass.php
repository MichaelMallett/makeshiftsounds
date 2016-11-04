<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\CreateClass.
 */

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\FixerBase;
use Pharborist\DocCommentNode;
use Pharborist\Objects\ClassNode;
use Pharborist\Parser;
use Pharborist\RootNode;
use Pharborist\Token;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @Fixer(
 *  id = "create_class"
 * )
 */
class CreateClass extends FixerBase {

  /**
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fs;

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fs = new Filesystem();
  }

  public function execute() {
    $ns = $this->extractNS($this->configuration['class']);
    $class = $this->extractLocal($this->configuration['class']);

    $doc = RootNode::create($ns);
    $ns = $doc->getNamespace($ns);
    Token::newline()->insertBefore($ns);
    Token::newline()->appendTo($ns);
    $class = ClassNode::create($class);

    if ($parent = $this->configuration['parent']) {
      Parser::parseSnippet('use ' . ltrim($parent, '\\') . ';')
        ->appendTo($ns)
        ->after(Token::newline());
      $class->setExtends($this->extractLocal($parent));
    }

    $interfaces = (array) $this->configuration['interfaces'];
    foreach ($interfaces as $interface) {
      Parser::parseSnippet('use ' . ltrim($interface, '\\') . ';')
        ->appendTo($ns)
        ->after(Token::newline());
    }
    $class->setImplements(array_map([ $this, 'extractLocal' ], $interfaces));

    if (isset($this->configuration['doc'])) {
      $class->setDocComment(DocCommentNode::create($this->configuration['doc']));
    }

    $class->appendTo($ns)->before(Token::newline());

    $destination = $this->getUnaliasedPath($this->configuration['destination']);
    $dir = subStr($destination, 0, strrPos($destination, '/'));
    $this->fs->mkdir($dir);
    file_put_contents($destination, $doc->getText());
    // Need to store the class' local name as its index identifier because
    // \Pharborist\Filter::isClass() doesn't support lookup by qualified path.
    $this->target->getIndexer('class')->addFile($destination);
  }

  protected function extractLocal($path) {
    return subStr($path, strrPos($path, '\\') + 1);
  }

  protected function extractNS($path) {
    $path = ltrim($path, '\\');
    return subStr($path, 0, strrPos($path, '\\'));
  }

}
