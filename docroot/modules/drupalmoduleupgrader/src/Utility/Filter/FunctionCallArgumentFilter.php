<?php

namespace Drupal\drupalmoduleupgrader\Utility\Filter;

use Pharborist\Functions\FunctionCallNode;
use Pharborist\Node;
use Pharborist\Variables\VariableNode;

/**
 * Filters for function calls which are passed a particular argument.
 */
class FunctionCallArgumentFilter {

  /**
   * @var string
   */
  protected $variable;

  public function __construct($variable) {
    $this->variable = $variable;
  }

  /**
   * @return boolean
   */
  public function __invoke(Node $node) {
    if ($node instanceof FunctionCallNode) {
      return (boolean) $node->getArgumentList()->children([$this, 'hasArgument'])->count();
    }
    return FALSE;
  }

  /**
   * @return boolean
   */
  public function hasArgument(Node $argument) {
    return ($argument instanceof VariableNode && $argument->getName() == $this->variable);
  }

}
