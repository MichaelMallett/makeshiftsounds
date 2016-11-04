<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\FormCallbackToMethod.
 */

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\FixerBase;

/**
 * @Fixer(
 *  id = "form_callback_to_method"
 * )
 */
class FormCallbackToMethod extends FixerBase {

  public function execute() {
    /** @var \Pharborist\Functions\FunctionDeclarationNode $callback */
    $callback = $this
      ->target
      ->getIndexer('function')
      ->get($this->configuration['callback']);

    list ($class, $method_name) = explode('::', $this->configuration['destination']);
    /** @var \Pharborist\Objects\ClassNode $class */
    $class = $this
      ->target
      ->getIndexer('class')
      ->get($class);

    $method = $callback->cloneAsMethodOf($class)->setName($method_name);

    $form_interface = new \ReflectionClass('\Drupal\Core\Form\FormInterface');
    if ($form_interface->hasMethod($method_name)) {
      $method->matchReflector($form_interface->getMethod($method_name));
    }

    $this->target->save($method);
  }

}
