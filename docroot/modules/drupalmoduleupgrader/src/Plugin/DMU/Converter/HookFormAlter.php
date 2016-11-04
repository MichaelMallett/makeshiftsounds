<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;

/**
 * @Converter(
 *  id = "hook_form_alter",
 *  description = @Translation("Corrects hook_form_alter() function signatures.")
 * )
 */
class HookFormAlter extends ConverterBase {

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    $indexer = $target->getIndexer('function');

    // @FIXME This is not working (returns empty result set)...don't know why.
    $alter_hooks = $indexer
      ->getQuery()
      ->condition(db_or()
        ->condition('id', $target->id() . '_form_alter')
        ->condition('id', db_like($target->id() . '_form_%_alter'), 'LIKE')
      )
      ->execute();

    foreach ($alter_hooks as $alter_hook) {
      /** @var \Pharborist\Functions\FunctionDeclarationNode $function */
      $function = $indexer->get($alter_hook->id);

      $parameters = $function->getParameters();
      if (sizeof($parameters) > 1) {
        $parameters[1]->setTypeHint('\Drupal\Core\Form\FormStateInterface');
        $target->save($function);
      }
    }
  }

}
