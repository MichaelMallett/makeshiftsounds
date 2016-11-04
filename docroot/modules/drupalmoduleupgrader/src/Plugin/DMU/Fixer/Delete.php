<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\Delete.
 */

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\FixerBase;

/**
 * @Fixer(
 *  id = "delete"
 * )
 */
class Delete extends FixerBase {

  use NodeCollectorTrait;

  public function execute() {
    foreach ($this->getObjects() as $node) {
      $node->remove();
    }
    $this->target->save();

    // Rebuild the index so it won't contain non-existent crap.
    $indexer = $this->target->getIndexer($this->configuration['type']);
    $indexer->clear();
    $indexer->build();
  }

}
