<?php

namespace Drupal\drupalmoduleupgrader;

use Pharborist\Functions\ParameterNode;

/**
 * Defines a parametric rewriter.
 *
 * Parametric rewriters are utility plugins which can alter a function body
 * in the context of a specific parameter. If a parameter is explicitly defined
 * as a node, for example, the rewriter can alter the function body so that
 * $node->nid becomes $node->id(). Rewriters work from property maps defined
 * in the plugin definition.
 */
interface RewriterInterface {

  /**
   * Parametrically rewrites the function containing the given parameter.
   *
   * @param ParameterNode $parameter
   *  The parameter upon which to base the rewrite. The parameter must be
   *  attached to a function or method declaration node, or fatal errors will
   *  likely result.
   */
  public function rewrite(ParameterNode $parameter);

}
