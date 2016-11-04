<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\drupalmoduleupgrader\IndexerBase;
use Drupal\drupalmoduleupgrader\IndexerExecutionInterface;
use Drupal\drupalmoduleupgrader\IndexerUsageInterface;
use Drupal\drupalmoduleupgrader\Utility\Filter\ContainsLogicFilter;
use Pharborist\Filter;
use Pharborist\Functions\FunctionDeclarationNode;
use Pharborist\NodeCollection;
use Pharborist\NodeInterface;
use Pharborist\Parser;

/**
 * @Indexer(
 *  id = "function"
 * )
 */
class Functions extends IndexerBase implements IndexerExecutionInterface, IndexerUsageInterface {

  protected function prepareID($id) {
    return preg_replace('/^hook_/', $this->target->id() . '_', $id);
  }

  /**
   * {@inheritdoc}
   */
  public function has($identifier) {
    return parent::has($this->prepareID($identifier));
  }

  /**
   * {@inheritdoc}
   */
  public function hasAny(array $identifiers) {
    return parent::hasAny(array_map([ $this, 'prepareID' ], $identifiers));
  }

  /**
   * {@inheritdoc}
   */
  public function hasAll(array $identifiers) {
    return parent::hasAll(array_map([ $this, 'prepareID' ], $identifiers));
  }

  /**
   * {@inheritdoc}
   */
  public function addFile($path) {
    $doc = Parser::parseFile($path);

    $doc
      ->children(Filter::isInstanceOf('\Pharborist\Functions\FunctionDeclarationNode'))
      ->each([ $this, 'add' ]);

    $doc
      ->find(Filter::isInstanceOf('\Pharborist\Functions\FunctionCallNode'))
      ->each([ $this, 'add' ]);
  }

  /**
   * {@inheritdoc}
   */
  public function add(NodeInterface $node) {
    /** @var \Pharborist\Functions\FunctionDeclarationNode|\Pharborist\Functions\FunctionCallNode $node */
    $fields = [
      'id' => (string) $node->getName(),
      'file' => $node->getFilename(),
      'type' => get_class($node),
    ];

    if ($node instanceof FunctionDeclarationNode) {
      $logical = new ContainsLogicFilter();
      $logical->whitelist('t');
      $logical->whitelist('drupal_get_path');
      $fields['has_logic'] = (int) $node->is($logical);
    }

    $this->db
      ->insert($this->table)
      ->fields($fields)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function delete($id) {
    parent::delete($this->prepareID($id));
  }

  /**
   * {@inheritdoc}
   */
  public function get($identifier) {
    $identifier = $this->prepareID($identifier);

    $file = $this->getQuery(['file'])
      ->condition('id', $identifier)
      ->execute()
      ->fetchField();

    return $this->target
      ->open($file)
      ->children(Filter::isFunction($identifier))
      ->get(0);
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(array $identifiers) {
    return parent::getMultiple(array_map([ $this, 'prepareID' ], $identifiers));
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
    $fields['has_logic'] = array(
      'type' => 'int',
      'size' => 'tiny',
      'unsigned' => TRUE,
    );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery(array $fields = []) {
    return parent::getQuery($fields)->condition('type', 'Pharborist\Functions\FunctionDeclarationNode');
  }

  /**
   * {@inheritdoc}
   */
  public function hasExecutable($identifier) {
    if ($this->has($identifier)) {
      $ret = $this->getQuery()
        ->condition('id', $this->prepareID($identifier))
        ->condition('has_logic', 0)
        ->countQuery()
        ->execute()
        ->fetchField();
      return $ret;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function execute($identifier, array $arguments = []) {
    $function = $this->prepareID($identifier);

    // If the function already exists, we can safely assume that it's already
    // been scanned for dangerous logic and evaluated into existence.
    if (function_exists($function)) {
      return call_user_func_array($function, $arguments);
    }
    else {
      if ($this->hasExecutable($function)) {
        eval($this->get($function)->get(0)->getText());
        return $this->execute($function, $arguments);
      }
      else {
        $variables = [
          '@function' => $function,
        ];
        throw new \LogicException(SafeMarkup::format('Cowardly refusing to execute @function.', $variables));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getUsages($identifier) {
    $function = $this->prepareID($identifier);

    $files = $this->getQuery(['file'])
      ->distinct()
      ->condition('id', $function)
      ->condition('type', 'Pharborist\Functions\FunctionCallNode')
      ->execute()
      ->fetchCol();

    $usages = new NodeCollection();
    foreach ($files as $file) {
      $this->target
        ->open($file)
        ->find(Filter::isFunctionCall($function))
        ->addTo($usages);
    }

    return $usages;
  }

}
