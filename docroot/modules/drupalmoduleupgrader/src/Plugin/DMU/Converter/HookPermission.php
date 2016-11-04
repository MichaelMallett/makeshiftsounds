<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\Component\Render\MarkupInterface;
use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;

/**
 * @Converter(
 *  id = "hook_permission",
 *  description = @Translation("Converts static implementations of hook_permission() to YAML."),
 *  hook = "hook_permission"
 * )
 */
class HookPermission extends ConverterBase {

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    $permissions = $this->executeHook($target, $this->pluginDefinition['hook']);
    $this->writeInfo($target, 'permissions', $this->castTranslatables($permissions));
  }

  /**
   * Casts translatable string objects in a permissions array to strings.
   *
   * @param array $permissions
   *   An array of permissions, as returned by hook_permission().
   *
   * @return array
   *   The permissions array, with all TranslatableString objects casted to
   *   strings.
   */
  protected function castTranslatables($permissions) {
    array_walk_recursive($permissions, function (&$value) {
      if ($value instanceof MarkupInterface) {
        $value = (string) $value;
      }
    });

    return $permissions;
  }

}
