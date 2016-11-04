<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\FixerInterface.
 */

namespace Drupal\drupalmoduleupgrader;

use Drupal\Core\Executable\ExecutableInterface;

/**
 * Interface implemented by all fixer plugins, which do small, isolated
 * modifications to a code base. They're basically PHP_CodeSniffer fixers
 * on steroids.
 */
interface FixerInterface extends ExecutableInterface {

  /**
   * Sets the target module to operate on.
   *
   * @param \Drupal\drupalmoduleupgrader\TargetInterface $target
   */
  public function setTarget(TargetInterface $target);

}
