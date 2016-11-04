<?php

namespace Drupal\drupalmoduleupgrader\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Plugin annotation object for parametric rewriters.
 *
 * Parametric rewriters are intelligent search-and-replace plugins which act
 * on a function body based on one of the function's parameters. The parameter
 * type must be known ahead of time.
 *
 * Plugin Namespace: Plugin\DMU\Rewriter
 *
 * @Annotation
 */
class Rewriter extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * Optional type hint to set on the parameter.
   *
   * @var
   */
  public $type_hint;

  /**
   * Properties known to the rewriter, keyed by property. Each property can
   * have 'get' and 'set' keys, which are the corresponding getter and setter
   * methods to replace the property with. The 'get' key is required; the
   * setter is only needed if it's possible to set the property at all (for
   * example, an entity ID property would not have a setter).
   *
   * @var array
   */
  public $properties = [];

}
