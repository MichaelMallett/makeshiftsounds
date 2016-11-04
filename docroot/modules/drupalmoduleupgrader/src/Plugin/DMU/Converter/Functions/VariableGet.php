<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\InstallStorage;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;
use Pharborist\Types\ScalarNode;

/**
 * @Converter(
 *  id = "variable_get",
 *  description = @Translation("Replaces variable_get() calls with Configuration API.")
 * )
 */
class VariableGet extends VariableAPI {

  /**
   * Default configuration values extracted from rewritten calls to
   * variable_get().
   *
   * @var mixed[]
   */
  private $defaults = [];

  /**
   * The schema accompanying any extracted default values.
   *
   * @var array
   */
  private $schema = [];

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    if ($this->tryRewrite($call, $target)) {
      $arguments = $call->getArguments();
      $key = $arguments[0]->toValue();

      if ($arguments[1] instanceof ScalarNode) {
        // @TODO Couldn't convert() derive the schema from $this->defaults?
        // That'd be preferable to having yet another state property ($schema)
        // on this class.
        $this->defaults[$key] = $arguments[1]->toValue();
        $this->schema[$key]['type'] = getType($this->defaults[$key]);
      }
      else {
        $comment = <<<END
Could not extract the default value because it is either indeterminate, or
not scalar. You'll need to provide a default value in
config/install/@module.settings.yml and config/schema/@module.schema.yml.
END;
        $variables = [ '@module' => $target->id() ];
        $this->buildFixMe($comment, $variables)->prependTo($call->getStatement());
      }

      return ClassMethodCallNode::create('\Drupal', 'config')
        ->appendArgument($target->id() . '.settings')
        ->appendMethodCall('get')
        ->appendArgument(clone $arguments[0]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    parent::convert($target);

    if ($this->defaults && $this->schema) {
      $group = $target->id() . '.settings';

      $this->write($target, InstallStorage::CONFIG_INSTALL_DIRECTORY . '/' . $group . '.yml', Yaml::encode($this->defaults));
      $this->defaults = [];

      $schema = [
        $group => [
          'type' => 'mapping',
          'label' => (string) $this->t('Settings'),
          'mapping' => $this->schema,
        ],
      ];
      $this->write($target, InstallStorage::CONFIG_SCHEMA_DIRECTORY . '/' . $target->id() . '.schema.yml', Yaml::encode($schema));
      $this->schema = [];
    }
  }

}
