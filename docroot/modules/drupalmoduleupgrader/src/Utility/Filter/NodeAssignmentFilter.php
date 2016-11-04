<?php

namespace Drupal\drupalmoduleupgrader\Utility\Filter;

use Pharborist\Filter;
use Pharborist\Node;

class NodeAssignmentFilter {

  /**
   * Tests if the given node is on the left side of an assignment.
   *
   * @param \Pharborist\Node $node
   *  The node to test.
   *
   * @return boolean
   */
  public function __invoke(Node $node) {
    /** @var \Pharborist\Operators\AssignNode $assignment */
    $assignment = $node->closest(Filter::isInstanceOf('\Pharborist\Operators\AssignNode'));
    return ($assignment ? $assignment->getLeftOperand() === $node : FALSE);
  }

}
