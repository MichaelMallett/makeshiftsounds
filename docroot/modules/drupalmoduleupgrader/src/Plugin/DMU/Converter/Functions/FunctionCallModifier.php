<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;

/**
 * Base class for converters which modify individual function calls.
 */
abstract class FunctionCallModifier extends ConverterBase {

  /**
   * Tries to rewrite the original function call.
   *
   * @param \Pharborist\Functions\FunctionCallNode $call
   *  The original function call.
   * @param \Drupal\drupalmoduleupgrader\TargetInterface $target
   *  The target module.
   *
   * @return \Pharborist\Node|NULL
   *  If the original function call is returned (determined by object identity),
   *  the function call is not replaced. If a different node is returned, it
   *  will replace the original call. And if nothing is returned, the original
   *  call is commented out with a FIXME.
   */
  abstract public function rewrite(FunctionCallNode $call, TargetInterface $target);

  /**
   * {@inheritdoc}
   */
  public function isExecutable(TargetInterface $target) {
    // Silence 'undefined index' notices if the 'function' key doesn't exist in
    // the plugin definition.
    $function = @($this->pluginDefinition['function'] ?: $this->getPluginId());
    return $target->getIndexer('function_call')->has($function);
  }

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    // Prevent stupid effing 'undefined index' notices.
    $function = @($this->pluginDefinition['function'] ?: $this->getPluginId());

    $function_calls = $target
      ->getIndexer('function_call')
      ->get($function);

    foreach ($function_calls as $function_call) {
      // If the function call is no longer attached to a tree, don't even
      // try to rewrite it. This could happen when there are two calls to
      // the same function in a single statement, and the first one has
      // been commented out -- the second one will be attached to an orphaned
      // sub-tree, and this will result in fatal errors.
      if (! $function_call->hasRoot()) {
        continue;
      }

      $rewritten = $this->rewrite($function_call, $target);
      if (empty($rewritten)) {
        $statement = $function_call->getStatement();
        $rewritten = $statement->toComment();
        $statement->replaceWith($rewritten);
        $this->buildFixMe()->insertBefore($rewritten);
      }
      elseif ($rewritten !== $function_call) {
        $function_call->replaceWith($rewritten);
      }

      $target->save($rewritten);
    }
  }

}
