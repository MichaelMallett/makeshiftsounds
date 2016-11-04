<?php

namespace Drupal\drupalmoduleupgrader;

interface IndexerExecutionInterface {

  /**
   * Returns if the specified index object can be evaluated and executed safely.
   *
   * @param string $id
   *  The object identifier.
   *
   * @return boolean
   */
  public function hasExecutable($id);

  public function execute($id, array $arguments = []);

}
