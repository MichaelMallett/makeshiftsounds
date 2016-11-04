<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Utility\Filter;

use Drupal\drupalmoduleupgrader\Utility\Filter\FunctionCallArgumentFilter;
use Drupal\Tests\UnitTestCase;
use Pharborist\Parser;

/**
 * @group DMU.Utility.Filter
 */
class FunctionCallArgumentFilterTest extends UnitTestCase {

  /**
   * @var \Drupal\drupalmoduleupgrader\Utility\Filter\FunctionCallArgumentFilter
   */
  protected $filter;

  public function setUp() {
    $this->filter = new FunctionCallArgumentFilter('foo');
  }

  public function testFailIfNotCallNode() {
    $this->assertFalse(Parser::parseExpression('$foo[0]')->is($this->filter));
  }

  public function testFailIfCallNotHasArgument() {
    $this->assertFalse(Parser::parseExpression('baz(0, "foo", bar())')->is($this->filter));
  }

  public function testFailIfVariableIsChild() {
    $this->assertFalse(Parser::parseExpression('baz($foo[0])')->is($this->filter));
  }

  public function testPass() {
    $this->assertTrue(Parser::parseExpression('baz($foo, 1, 2, "bar")')->is($this->filter));
    $this->assertTrue(Parser::parseExpression('baz(1, 2, $foo, "bar")')->is($this->filter));
  }

}
