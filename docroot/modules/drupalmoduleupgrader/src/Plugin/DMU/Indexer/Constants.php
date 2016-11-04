<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\Constants.
 */

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer;

use Drupal\drupalmoduleupgrader\ArrayIndexer;
use Drupal\drupalmoduleupgrader\IndexerUsageInterface;
use Pharborist\Constants\ConstantDeclarationNode;
use Pharborist\Constants\ConstantNode;
use Pharborist\Filter;
use Pharborist\Functions\DefineNode;
use Pharborist\NodeCollection;
use Pharborist\NodeInterface;
use Pharborist\Parser;
use Pharborist\Types\ScalarNode;
use Pharborist\Types\StringNode;

/**
 * @Indexer(
 *  id = "constant"
 * )
 */
class Constants extends ArrayIndexer implements IndexerUsageInterface {

  /**
   * {@inheritdoc}
   */
  public function addFile($path) {
    Parser::parseFile($path)
      ->find(Filter::isInstanceOf('\Pharborist\Constants\ConstantNode', '\Pharborist\Functions\DefineNode', '\Pharborist\Constants\ConstantDeclarationNode'))
      ->each([ $this, 'add' ]);
  }

  /**
   * {@inheritdoc}
   */
  public function add(NodeInterface $node) {
    if ($node instanceof DefineNode) {
      list ($key, $value) = $node->getArguments();
      if ($key instanceof StringNode && $value instanceof ScalarNode) {
        $this->elements[ $key->toValue() ] = $value->toValue();
      }
    }
    elseif ($node instanceof ConstantDeclarationNode) {
      $value = $node->getValue();
      if ($value instanceof ScalarNode) {
        $this->elements[ (string) $node->getName() ] = $value->toValue();
      }
    }
    elseif ($node instanceof ConstantNode) {
      $this->db
        ->insert($this->table)
        ->fields([
          'id' => (string) $node->getConstantName(),
          // Hardcoding file name, as file name resolution is unavailable due
          // to changes upstream in Pharborist.
          'file' => 'foo.module',
        ])
        ->execute();
    }
    else {
      throw new \InvalidArgumentException('Unexpected node type (expected DefineNode, ConstantDeclarationNode, or ConstantNode).');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getUsages($identifier) {
    $files = $this->getQuery(['file'])
      ->distinct()
      ->condition('id', $identifier)
      ->execute()
      ->fetchCol();

    $usages = new NodeCollection();
    foreach ($files as $file) {
      $this->target
        ->open($file)
        ->find(Filter::isInstanceOf('\Pharborist\Constants\ConstantNode'))
        ->filter(function(ConstantNode $node) use ($identifier) {
          return $node->getConstantName() == $identifier;
        })
        ->addTo($usages);
    }

    return $usages;
  }

}
