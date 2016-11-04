<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Drupal\drupalmoduleupgrader\Utility\StringTransformTrait;

/**
 * @Converter(
 *  id = "hook_entity_info",
 *  description = @Translation("Creates entity class boilerplate from hook_entity_info()."),
 *  hook = "hook_entity_info"
 * )
 */
class HookEntityInfo extends ConverterBase {

  use StringTransformTrait;

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    try {
      $entity_types = $this->executeHook($target, 'entity_info');
    }
    catch (\LogicException $e) {
      $this->log->warning($e->getMessage(), [
        'target' => $target->id(),
        'hook' => $this->pluginDefinition['hook'],
      ]);
      return;
    }

    foreach ($entity_types as $id => $entity_type) {
      $entity_type['id'] = $id;

      $entity_type['base_table'] = $entity_type['base table'];
      unset($entity_type['base table']);

      $entity_type['keys'] = $entity_type['entity keys'];
      unset($entity_type['entity keys']);

      if (isset($entity_type['controller class'])) {
        /** @var \Pharborist\Objects\ClassNode $controller */
        $indexer = $target->getIndexer('class');
        if ($indexer->has($entity_type['controller class'])) {
          $controller = $indexer->get($entity_type['controller class']);

          $parent = $controller->getExtends();
          if ($parent) {
            if ($parent->getText() == 'DrupalDefaultEntityController' || $parent->getText() == 'EntityAPIController') {
              $controller->setExtends('Drupal\Core\Entity\Sql\SqlContentEntityStorage');
            }
            else {
              // @todo Not entirely sure what to do here. It's not a huge problem
              // if the controller extends another class defined by the target
              // (which is, admittedly, an edge case), but if it extends a
              // controller defined by *another* module that isn't Entity API?
            }
          }

          // @todo Handle interfaces implemented by the entity controller.

          $this->writeClass($target, PSR4::toPSR4($target, $controller));
          $entity_type['controllers']['storage'] = $controller->getName()->getAbsolutePath();
        }
        else {
          throw new \LogicException(SafeMarkup::format('Cannot get ahold of the controller class for @entity_type entity type.', ['@entity_type' => $id]));
        }
      }
      else {
        $entity_type['controllers']['storage'] = 'Drupal\Core\Entity\Sql\SqlContentEntityStorage';
      }

      $render = [
        '#module' => $target->id(),
        '#class' => $this->toTitleCase($id),
        '#theme' => 'dmu_entity_type',
        '#info' => $entity_type,
      ];
      $this->writeClass($target, $this->parse($render));
    }
  }

}
