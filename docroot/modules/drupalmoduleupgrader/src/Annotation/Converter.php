<?php

namespace Drupal\drupalmoduleupgrader\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Plugin annotation object for DMU converter plugins.
 *
 * Converters take Drupal 7 code and do what they can to rewrite it for Drupal 8.
 * When a converter cannot convert something, it can leave a FIXME notice at the
 * affected code informing the developer what still needs to be done. Converters
 * may generate ugly code, but refactoring is not their job. Converts modify the
 * target module in place.
 *
 * Plugin Namespace: Plugin\DMU\Converter
 *
 * @Annotation
 */
class Converter extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * A short description of the conversion the plugin performs.
   *
   * @var string
   */
  public $description;

  /**
   * If the plugin converts a hook (or several hooks), the hook(s) it converts
   * (without the hook_ prefix).
   *
   * @var string|string[]
   */
  public $hook;

  /**
   * Optional FIXME notice the converter should leave at code that it cannot convert.
   *
   * @var string
   */
  public $fixme;

  /**
   * Optional documentation links to be included in the FIXME notice.
   *
   * @var string[]
   */
  public $documentation = [];

}
