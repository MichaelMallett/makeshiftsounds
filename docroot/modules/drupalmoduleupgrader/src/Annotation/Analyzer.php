<?php

namespace Drupal\drupalmoduleupgrader\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Plugin annotation object for DMU analyzer plugins.
 *
 * Analyzers scan a target module's code to determine if any problems exist. If
 * any do exist, it's the analyzer's job to file an issue detailing the nature
 * of the problem -- summarizing the issue, pointing out where the problem is
 * found, and referring the developer to documentation on drupal.org explaining
 * how to fix the problem.
 *
 * Plugin Namespace: Plugin\DMU\Analyzer
 *
 * @Annotation
 */
class Analyzer extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * A short description of the analysis the plugin performs.
   *
   * @var string
   */
  public $description;

  /**
   * Documentation describing the changes covered by the plugin. Each item
   * in the array should be an array with 'url' and 'title' keys.
   *
   * @var array[]
   */
  public $documentation = [];

  /**
   * The issue title. Markdown and HTML are allowed.
   *
   * @var string
   */
  public $title;

  /**
   * An optional detailed summary of the issue. Markdown and HTML are allowed.
   *
   * @var string
   */
  public $summary;
  
  /**
   * The default tags to be applied to flagged issues. Tags are fairly arbitrary and
   * can be any value. Tags are divided into groups (i.e., the keys of this here
   * array).
   *
   * @var array
   */
  public $tags = [];

}
