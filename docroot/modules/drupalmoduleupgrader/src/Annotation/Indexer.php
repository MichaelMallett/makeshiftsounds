<?php

namespace Drupal\drupalmoduleupgrader\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Plugin annotation object for DMU indexer plugins.
 *
 * Indexers scan a target module to determine what's in it so that other plugins
 * can use that information. All available indexers are always run before any
 * other plugin type. Indexers are responsible for cataloguing things like:
 *
 * - What hooks a module implements, and where those implementations reside (i.e.,
 *   which files)
 * - Classes defined by a module
 * - Functions defined by a module
 * - Tests defined by a module, and what kind of tests they are
 * - Which functions are called by the module, and when
 *
 * Any information gathered by an indexer is available to other plugin types.
 * Essentially, indexers build a "map" of a target module, which is stored in
 * an index backend (by default, an SQLite database that lives only in memory).
 *
 * Plugin Namespace: Plugin\DMU\Indexer
 *
 * @Annotation
 */
class Indexer extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

}
