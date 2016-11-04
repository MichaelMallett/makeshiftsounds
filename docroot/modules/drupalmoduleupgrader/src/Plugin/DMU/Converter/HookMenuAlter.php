<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\ParameterNode;

/**
 * @Converter(
 *  id = "hook_menu_alter",
 *  description = @Translation("Creates boilerplate for logic that formerly belonged in hook_menu_alter()."),
 *  hook = "hook_menu_alter",
 *  fixme = @Translation("hook_menu_alter() is gone in Drupal 8. You will have to port its
functionality manually. The are several mechanisms for this:

To alter routes, you must implement a route subscriber class. An empty one
has been generated for you in src/Routing/RouteSubscriber.php.

To alter menu link definitions, see hook_menu_links_discovered_alter(). An
empty implementation has been created at the end of this file.

To alter local task definitions, see hook_menu_local_tasks_alter(). An
empty implementation has been created for you at the end of this file.

To alter local actions, see hook_menu_local_actions_alter(). An
empty implementation has been created for you at the end of this file.

Contextual links are altered during rendering only. See
hook_contextual_links_view_alter(). An empty implementation has been
created for you at the end of this file."),
 *  documentation = {
 *    "https://www.drupal.org/node/2118147#alter",
 *    "https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Menu%21menu.api.php/function/hook_menu_links_discovered_alter/8",
 *    "https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Menu%21menu.api.php/function/hook_menu_local_tasks_alter/8",
 *    "https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Menu%21menu.api.php/function/hook_menu_local_actions_alter/8",
 *    "https://api.drupal.org/api/drupal/core%21modules%21contextual%21contextual.api.php/function/hook_contextual_links_view_alter/8",
 *  }
 * )
 */
class HookMenuAlter extends ConverterBase {

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    $target
      ->getIndexer('function')
      ->get($this->pluginDefinition['hook'])
      ->setDocComment($this->buildFixMe(NULL, [], self::DOC_COMMENT));

    $render = [
      '#theme' => 'dmu_route_subscriber',
      '#module' => $target->id(),
    ];
    $this->writeClass($target, $this->parse($render));

    $alterable = ParameterNode::create('data');
    $alterable->setTypeHint('array')->setReference(TRUE);

    $parameter = clone $alterable;
    $this
      ->implement($target, 'menu_links_discovered_alter')
      ->appendParameter($parameter->setName('links'));

    $parameter = clone $alterable;
    $this
      ->implement($target, 'menu_local_tasks_alter')
      ->appendParameter($parameter->setName('data'))
      ->appendParameter(ParameterNode::create('route_name'));

    $parameter = clone $alterable;
    $this
      ->implement($target, 'menu_local_actions_alter')
      ->appendParameter($parameter->setName('local_actions'));

    $parameter = clone $alterable;
    $items = clone $alterable;
    $function = $this
      ->implement($target, 'contextual_links_view_alter')
      ->appendParameter($parameter->setName('element'))
      ->appendParameter($items->setName('items')->setReference(FALSE));

    $target->save($function);
  }

}
