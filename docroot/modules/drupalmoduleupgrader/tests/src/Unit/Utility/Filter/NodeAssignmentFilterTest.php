<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Utility\Filter;

use Drupal\drupalmoduleupgrader\Utility\Filter\NodeAssignmentFilter;
use Drupal\Tests\UnitTestCase;
use Pharborist\Parser;

/**
 * @group DMU.Utility.Filter
 */
class NodeAssignmentFilterTest extends UnitTestCase {

  /**
   * @var \Drupal\drupalmoduleupgrader\Utility\Filter\NodeAssignmentFilter
   */
  protected $filter;

  public function setUp() {
    $this->filter = new NodeAssignmentFilter();
  }

  public function testLeftOperand() {
    /** @var \Pharborist\Operators\AssignNode $expr */
    $expr = Parser::parseExpression('$foo = "bazzz"');
    $this->assertTrue($expr->getLeftOperand()->is($this->filter));
  }

  public function testRightOperand() {
    /** @var \Pharborist\Operators\AssignNode $expr */
    $expr = Parser::parseExpression('$baz = $foo');
    $this->assertFalse($expr->getRightOperand()->is($this->filter));
  }

}
