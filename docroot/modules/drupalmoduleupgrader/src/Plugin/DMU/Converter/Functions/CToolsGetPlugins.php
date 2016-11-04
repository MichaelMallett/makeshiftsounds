<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;
use Pharborist\Types\StringNode;

/**
 * @Converter(
 *  id = "ctools_get_plugins",
 *  description = @Translation("Rewrites calls to ctools_get_plugins().")
 * )
 */
class CToolsGetPlugins extends FunctionCallModifier {

  /**
   * Tests if the function call can be rewritten at all, which it will be
   * only if both arguments are strings, and the first argument is the machine
   * name of the target module.
   *
   * @param \Pharborist\Functions\FunctionCallNode $call
   *  The function call to test.
   * @param \Drupal\drupalmoduleupgrader\TargetInterface $target
   *  The target module.
   *
   * @return boolean
   */
  public function canRewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();
    return ($arguments[0] instanceof StringNode && $arguments[0]->toValue() == $target->id() && $arguments[1] instanceof StringNode);
  }

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    if (! $this->canRewrite($call, $target)) {
      return NULL;
    }

    $arguments = $call->getArguments();
    $plugin_owner = $arguments[0]->toValue();
    $plugin_type = $arguments[1]->toValue();

    $services = $target->getServices();
    $service_id = 'plugin.manager.' . $plugin_owner . '.' . $plugin_type;
    $services->set($service_id, [
      'class' => 'Drupal\Core\Plugin\DefaultPluginManager',
      'arguments' => [
        'Plugin/' . $plugin_owner . '/' . $plugin_type,
        '@container.namespaces',
        '@module_handler',
        'Drupal\Component\Plugin\PluginBase',
        'Drupal\Component\Annotation\Plugin',
      ],
    ]);
    $this->writeInfo($target, 'services', [ 'services' => $services->toArray() ]);

    return ClassMethodCallNode::create('\Drupal', 'service')
      ->appendArgument($service_id)
      ->appendMethodCall('getDefinitions');
  }

}
