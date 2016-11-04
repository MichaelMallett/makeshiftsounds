<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\DocCommentNode;

/**
 * @Converter(
 *  id = "hook_field_attach_delete_bundle",
 *  description = @Translation("Adds a FIXME notice to hook_field_attach_delete_bundle()."),
 *  hook = "hook_field_attach_delete_bundle",
 *  fixme = @Translation("@FIXME
hook_field_attach_delete_bundle() has been renamed to hook_entity_bundle_delete(),
and it no longer accepts an $instances argument. This cannot be converted
automatically because it's likely to affect the hook's logic, so you'll need to
modify this function manually.

For more information, see https://www.drupal.org/node/1964766.
")
 * )
 */
class HookFieldAttachDeleteBundle extends ConverterBase {

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    $hook = $target
      ->getIndexer('function')
      ->get($this->pluginDefinition['hook'])
      ->before(DocCommentNode::create($this->pluginDefinition['fixme']));

    $target->save($hook);
  }

}
