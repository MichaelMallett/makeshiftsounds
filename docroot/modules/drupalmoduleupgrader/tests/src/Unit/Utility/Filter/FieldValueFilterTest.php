<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Utility\Filter;

use Drupal\drupalmoduleupgrader\Utility\Filter\FieldValueFilter;
use Drupal\Tests\UnitTestCase;
use Pharborist\Parser;

/**
 * @group DMU.Utility.Filter
 */
class FieldValueFilterTest extends UnitTestCase {

  /**
   * @var \Drupal\drupalmoduleupgrader\Utility\Filter\FieldValueFilter
   */
  protected $filter;

  public function setUp() {
    $this->filter = new FieldValueFilter('foo');
  }

  public function testFailIfNotArrayLookupNode() {
    $this->assertFalse(Parser::parseExpression('$foo->baz')->is($this->filter));
  }

  public function testFailIfLookupRootIsNotObjectPropertyNode() {
    $this->assertFalse(Parser::parseExpression('$foo["bar"]["baz"]')->is($this->filter));
  }

  public function testFailOnVariableNameMismatch() {
    $this->assertFalse(Parser::parseExpression('$baz->foo["und"][0]["value"]')->is($this->filter));
  }

  public function testPass() {
    $this->assertTrue(Parser::parseExpression('$foo->field_baz["und"][0]["value"]')->is($this->filter));
  }

}
