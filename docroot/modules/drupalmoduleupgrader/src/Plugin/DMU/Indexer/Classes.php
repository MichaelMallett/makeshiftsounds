<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer;

use Drupal\drupalmoduleupgrader\IndexerBase;
use Drupal\drupalmoduleupgrader\IndexerUsageInterface;
use Pharborist\Filter;
use Pharborist\NodeCollection;
use Pharborist\NodeInterface;
use Pharborist\Objects\ClassNode;
use Pharborist\Objects\NewNode;
use Pharborist\Parser;

/**
 * @Indexer(
 *  id = "class"
 * )
 */
class Classes extends IndexerBase implements IndexerUsageInterface {

  /**
   * {@inheritdoc}
   */
  public function addFile($path) {
    $doc = Parser::parseFile($path);

    $doc
      ->find(Filter::isInstanceOf('\Pharborist\Objects\ClassNode'))
      ->each([ $this, 'add' ]);

    $doc
      ->find(Filter::isInstanceOf('\Pharborist\Objects\NewNode'))
      ->each([ $this, 'add' ]);
  }

  /**
   * {@inheritdoc}
   */
  public function add(NodeInterface $node) {
    $fields = [
      'file' => $node->getFilename(),
      'type' => get_class($node),
    ];

    if ($node instanceof ClassNode) {
      $fields['id'] = (string) $node->getName();
      $fields['parent'] = (string) $node->getExtends();
    }
    elseif ($node instanceof NewNode) {
      $fields['id'] = (string) $node->getClassName();
    }
    else {
      throw new \InvalidArgumentException('Unexpected node type (expected ClassNode or NewNode).');
    }

    $this->db->insert($this->table)->fields($fields)->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function get($identifier) {
    $file = $this->getQuery(['file'])
      ->condition('id', $identifier)
      ->execute()
      ->fetchField();

    return $this->target
      ->open($file)
      ->find(Filter::isClass($identifier))
      ->get(0);
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    $fields = parent::getFields();

    $fields['type'] = array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => TRUE,
    );
    $fields['parent'] = array(
      'type' => 'varchar',
      'length' => 255,
    );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getUsages($identifier) {
    $files = $this->getQuery(['file'])
      ->distinct()
      ->condition('id', $identifier)
      ->condition('type', 'Pharborist\Objects\NewNode')
      ->execute()
      ->fetchCol();

    $usages = new NodeCollection();
    foreach ($files as $file) {
      $this->target
        ->open($file)
        ->find(Filter::isInstanceOf('\Pharborist\Objects\NewNode'))
        ->filter(function(NewNode $node) use ($identifier) {
          return $node->getClassName() == $identifier;
        })
        ->addTo($usages);
    }

    return $usages;
  }

}
