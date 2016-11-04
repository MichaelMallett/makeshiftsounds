<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\Disable.
 */

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer;

/**
 * @Fixer(
 *  id = "disable"
 * )
 */
class Disable extends Notify {

  use NodeCollectorTrait;

  /**
   * {@inheritdoc}
   */
  public function execute() {
    parent::execute();

    foreach ($this->getObjects() as $node) {
      if ($node->hasRoot()) {
        $statement = $node->getStatement();
        $statement->replaceWith($statement->toComment());
      }
    }

    $this->target->save();
  }

}
