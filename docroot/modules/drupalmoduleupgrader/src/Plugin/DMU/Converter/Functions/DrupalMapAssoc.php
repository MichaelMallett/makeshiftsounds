<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;

/**
 * @Converter(
 *  id = "drupal_map_assoc",
 *  description = @Translation("Rewrites calls to drupal_map_assoc().")
 * )
 */
class DrupalMapAssoc extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    // Change function name to array_combine().
    $call->setName('array_combine');

    // Duplicate the first $array argument twice (silly, but true).
    // Need to clone the argument to make a copy of it, since Pharborist works
    // on original tree elements.
    $arguments = $call->getArguments();
    return $call->appendArgument(clone $arguments[0]);
  }

}
