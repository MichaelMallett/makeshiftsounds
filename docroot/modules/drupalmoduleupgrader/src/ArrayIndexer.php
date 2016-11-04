<?php

namespace Drupal\drupalmoduleupgrader;

abstract class ArrayIndexer extends IndexerBase {

  protected $elements = [];

  /**
   * {@inheritdoc}
   */
  final public function hasAny(array $keys) {
    foreach ($keys as $key) {
      if ($this->count($key)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  final public function hasAll(array $keys) {
    foreach ($keys as $key) {
      if ($this->count($key) == 0) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  final public function get($key) {
    return $this->elements[$key];
  }

  /**
   * {@inheritdoc}
   */
  final public function getMultiple(array $keys) {
    $values = array();

    foreach ($keys as $key) {
      if (array_key_exists($key, $this->elements)) {
        $values[$key] = $this->get($key);
      }
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  final public function getAll() {
    return $this->elements;
  }

}
