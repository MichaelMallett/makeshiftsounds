<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Types\StringNode;

/**
 * @Converter(
 *  id = "_db",
 *  deriver = "\Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\DBDeriver"
 * )
 */
class DB extends FunctionCallModifier {

  /**
   * Tables which will cause the function call to be commented out.
   *
   * @var string[]
   */
  protected static $forbiddenTables = ['system', 'variable'];

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $table = $call->getArgumentList()->getItem(0);
    return ($table instanceof StringNode && in_array($table->toValue(), self::$forbiddenTables)) ? NULL : $call;
  }

}
