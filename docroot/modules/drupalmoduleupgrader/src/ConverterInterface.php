<?php

namespace Drupal\drupalmoduleupgrader;

/**
 * Interface implemented by all plugins which can modify a Drupal 7 module and
 * convert part of it to Drupal 8.
 */
interface ConverterInterface {

  /**
   * Returns if this conversion applies to the target module. If FALSE,
   * the convert() method will not be called.
   *
   * @param TargetInterface $target
   *  The target module.
   *
   * @return boolean
   */
  public function isExecutable(TargetInterface $target);

  /**
   * Performs required conversions.
   *
   * @param TargetInterface $target
   *  The target module to convert.
   */
  public function convert(TargetInterface $target);

}
