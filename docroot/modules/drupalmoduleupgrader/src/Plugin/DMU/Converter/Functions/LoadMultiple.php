<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;

/**
 * @Converter(
 *  id = "_load_multiple",
 *  deriver = "\Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\LoadMultipleDeriver"
 * )
 */
class LoadMultiple extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();

    // If there were three arguments, the call is affecting the internal
    // entity cache. Unfortunately, it's pretty much impossible to reliably
    // determine whether or not they wanted to reset the cache, so let's just
    // leave a FIXME.
    if (sizeof($arguments) == 3) {
      $variables = [
        '!entity_type' => $this->pluginDefinition['entity_type'],
      ];
      $this->buildFixMe('To reset the !entity_type cache, use EntityStorageInterface::resetCache().', $variables)->insertBefore($call);
    }

    $rewritten = ClassMethodCallNode::create('\Drupal', 'entityManager')
      ->appendMethodCall('getStorage')
      ->appendArgument($this->pluginDefinition['entity_type']);

    // If there's more than one argument, conditions were passed (not a
    // recommended practice, but modules might have done it anyway), in which
    // case we need to use loadByProperties(). Otherwise, loadMultiple().
    if (sizeof($arguments) > 1) {
      return $rewritten
        ->appendMethodCall('loadByProperties')
        ->appendArgument(clone $arguments[1]);
    }
    else {
      return $rewritten
        ->appendMethodCall('loadMultiple')
        ->appendArgument(clone $arguments[0]);
    }
  }

}
