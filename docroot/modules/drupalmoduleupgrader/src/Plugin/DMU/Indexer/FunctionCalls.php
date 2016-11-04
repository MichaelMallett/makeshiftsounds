<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer;

use Drupal\drupalmoduleupgrader\IndexerBase;
use Pharborist\Filter;
use Pharborist\Parser;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\NodeCollection;

/**
 * @Indexer(
 *  id = "function_call",
 *  description = @Translation("Indexes all function calls in a target module."),
 *  exclude = { "t" }
 * )
 */
class FunctionCalls extends IndexerBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Symfony\Component\Finder\SplFileInfo $file */
    foreach ($this->target->getFinder() as $file) {
      $path = $file->getPathname();

      $this->target
        ->open($path)
        ->find(Filter::isInstanceOf('\Pharborist\Functions\FunctionCallNode'))
        ->not(function(FunctionCallNode $function_call) {
          return in_array($function_call->getName()->getText(), $this->pluginDefinition['exclude']);
        })
        ->each([ $this, 'add' ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function get($id) {
    $all = new NodeCollection([]);

    $files = $this
      ->getQuery(['file'])
      ->distinct(TRUE)
      ->condition('id', $id)
      ->execute()
      ->fetchCol();

    array_walk($files, function($file) use ($all, $id) {
      $all->add($this->target->open($file)->find(Filter::isFunctionCall($id)));
    });

    return $all;
  }

  /**
   * {@inheritdoc}
   */
  public function addFile($path) {
    $doc = Parser::parseFile($path);

    $doc
      ->find(Filter::isInstanceOf('\Pharborist\Functions\FunctionCallNode'))
      ->each([ $this, 'add' ]);
  }

}
