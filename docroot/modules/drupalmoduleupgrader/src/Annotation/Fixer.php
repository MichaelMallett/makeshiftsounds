<?php

/**
 * @file
 * Contains Drupal\drupalmoduleupgrader\Annotation\Fixer.
 */

namespace Drupal\drupalmoduleupgrader\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Plugin annotation object for DMU fixer plugins.
 *
 * Fixers are similar in nature to the fixer classes used by PHP_CodeSniffer,
 * in the sense that their job is to perform particular, isolated changes
 * to code. But DMU fixers are a lot more powerful than PHPCS's because a)
 * they're Drupal plugins, and b) they're using Pharborist.
 *
 * Plugin Namespace: Plugin\DMU\Fixer
 *
 * @Annotation
 */
class Fixer extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

}
