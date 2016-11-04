<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;

/**
 * @Converter(
 *  id = "form_state_defaults",
 *  description = @Translation("Rewrites calls to form_state_defaults().")
 * )
 */
class FormStateDefaults extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    // @todo
    // There are two possibilities here. If the call is part of a += operation
    // or an array_merge() call, the entire thing needs to be commented out.
    // Otherwise, it should be changed to 'new FormState()', which requires an
    // upstream change in Pharborist.
  }

}
