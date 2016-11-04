<?php

namespace Drupal\drupalmoduleupgrader\Utility\Filter;

use Pharborist\Filter;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\ParentNode;

class ContainsLogicFilter {

  /**
   * Function calls which should not be considered logic.
   *
   * @var string[]
   */
  protected $whitelist = [];

  /**
   * Pharborist node types which are considered logic.
   *
   * @var string[]
   */
  protected static $logic = [
    '\Pharborist\ControlStructures\IfNode',
    '\Pharborist\ControlStructures\SwitchNode',
    '\Pharborist\Objects\ClassMethodCallNode',
    '\Pharborist\Objects\ObjectMethodCallNode',
    '\Pharborist\Objects\NewNode',
    '\Pharborist\Objects\ClassConstantLookupNode',
  ];

  /**
   * Specify a function to be whitelisted so that it will not be considered
   * logic.
   *
   * @param string ... $function
   *  At least one function to add to the whitelist.
   */
  public function whitelist() {
    $this->whitelist = array_unique(array_merge($this->whitelist, func_get_args()));
  }

  /**
   * Tests if a function contains logic: any branching operator, function
   * call, or object instantiation.
   *
   * @param \Pharborist\ParentNode $node
   *  The node to test.
   *
   * @return boolean
   */
  public function __invoke(ParentNode $node) {
    $function_calls = $node
      ->find(Filter::isInstanceOf('\Pharborist\Functions\FunctionCallNode'))
      ->not(function(FunctionCallNode $call) {
        return in_array($call->getName()->getText(), $this->whitelist);
      });

    if ($function_calls->isEmpty()) {
      $filter = call_user_func_array('\Pharborist\Filter::isInstanceOf', static::$logic);
      return (boolean) $node->find($filter)->count();
    }
    else {
      return TRUE;
    }
  }

}
