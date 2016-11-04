<?php

namespace Drupal\drupalmoduleupgrader;

use Pharborist\NodeInterface;

/**
 * Interface for plugins which can scan a target module to collect information
 * about what it contains. Indexers are always run before other plugin types,
 * and all available indexers are always run. All information collected by
 * indexers is available to the other plugin types via TargetInterface's
 * getIndexer() method.
 */
interface IndexerInterface {

  public function bind(TargetInterface $module);

  public function build();

  public function clear();

  public function destroy();

  public function has($identifier);

  public function hasAny(array $identifiers);

  public function hasAll(array $identifiers);

  public function addFile($path);

  public function add(NodeInterface $node);

  public function deleteFile($path);

  public function delete($identifier);

  public function get($identifier);

  public function getMultiple(array $identifiers);

  public function getAll();

  public function getFields();

  public function getQuery(array $fields = []);

}
