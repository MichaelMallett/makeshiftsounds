<?php

namespace Drupal\drupalmoduleupgrader;

use Drupal\Core\Database\Connection as DatabaseConnection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase as CorePluginBase;
use Pharborist\NodeCollection;
use Pharborist\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for indexers.
 */
abstract class IndexerBase extends CorePluginBase implements IndexerInterface, ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * @var TargetInterface
   */
  protected $target;

  /**
   * @var string
   */
  protected $table;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, DatabaseConnection $db, TargetInterface $target = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->db = $db;

    if ($target) {
      $this->bind($target);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function bind(TargetInterface $module) {
    $this->target = $module;
    $this->table = $module->id() . '__' . $this->getPluginId();

    $schema = $this->db->schema();
    if ($schema->tableExists($this->table)) {
      $this->clear();
    }
    else {
      $schema->createTable($this->table, [ 'fields' => $this->getFields() ]);
    }
    $this->build();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Symfony\Component\Finder\SplFileInfo $file */
    foreach ($this->target->getFinder() as $file) {
      $this->addFile($file->getPathname());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clear() {
    $this->db->truncate($this->table)->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function destroy() {
    $this->db->schema()->dropTable($this->table);
  }

  /**
   * {@inheritdoc}
   */
  public function has($identifier) {
    return (boolean) $this->getQuery()
      ->condition('id', $identifier)
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function hasAny(array $identifiers) {
    return $this->has($identifiers);
  }

  /**
   * {@inheritdoc}
   */
  public function hasAll(array $identifiers) {
    $count = $this->getQuery()
      ->condition('id', $identifiers)
      ->countQuery()
      ->execute()
      ->fetchField();

    return ($count == sizeof(array_unique($identifiers)));
  }

  /**
   * {@inheritdoc}
   */
  public function add(NodeInterface $node) {
    $this->db
      ->insert($this->table)
      ->fields([
        'id' => (string) $node->getName(),
        'file' => $node->getFilename(),
      ])
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteFile($path) {
    $this->db
      ->delete($this->table)
      ->condition('file', $path)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function delete($identifier) {
    $this->db
      ->delete($this->table)
      ->condition('id', $identifier)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(array $identifiers) {
    return new NodeCollection(array_filter(array_map([ $this, 'get' ], $identifiers)));
  }

  /**
   * {@inheritdoc}
   */
  public function getAll() {
    return $this->getMultiple($this->getQuery(['id'])->distinct()->execute()->fetchCol());
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    return [
      'id' => [
        'type' => 'varchar',
        'length' => 255,
        'not null' => TRUE,
      ],
      'file' => [
        'type' => 'varchar',
        'length' => 255,
        'not null' => TRUE,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery(array $fields = []) {
    return $this->db
      ->select($this->table)
      ->fields($this->table, $fields);
  }

}
