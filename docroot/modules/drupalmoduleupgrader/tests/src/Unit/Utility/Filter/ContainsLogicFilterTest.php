<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Utility\Filter;

use Drupal\drupalmoduleupgrader\Utility\Filter\ContainsLogicFilter;
use Drupal\Tests\UnitTestCase;
use Pharborist\Parser;

/**
 * @group DMU.Utility.Filter
 */
class ContainsLogicFilterTest extends UnitTestCase {

  /**
   * @var \Drupal\drupalmoduleupgrader\Utility\Filter\ContainsLogicFilter
   */
  protected $filter;

  public function setUp() {
    $this->filter = new ContainsLogicFilter();
  }

  public function testFunctionCallIsLogic() {
    $this->assertTrue(Parser::parseSnippet('function foo() { bar(); }')->is($this->filter));
  }

  public function testWhiteListedFunctionCallIsNotLogic() {
    $this->filter->whitelist('bar');
    $this->assertFalse(Parser::parseSnippet('function foo() { bar(); }')->is($this->filter));
  }

  public function testIfIsLogic() {
    $this->assertTrue(Parser::parseSnippet('function foo() { if (true) return TRUE; }')->is($this->filter));
  }

  public function testSwitchIsLogic() {
    $function = <<<'END'
function foo() {
  switch ($baz) {
    case 'a':
    case 'b':
    default:
      break;
  }
}
END;
    $this->assertTrue(Parser::parseSnippet($function)->is($this->filter));
  }

  public function testClassMethodCallIsLogic() {
    $this->assertTrue(Parser::parseSnippet('function foo() { return \Drupal::config(); }')->is($this->filter));
  }

  public function testObjectMethodCallIsLogic() {
    $this->assertTrue(Parser::parseSnippet('function foo() { return \Drupal::config()->get("foo.settings"); }')->is($this->filter));
  }

  public function testObjectInstantiationIsLogic() {
    $this->assertTrue(Parser::parseSnippet('function foo() { return new Entity(); }')->is($this->filter));
  }

}
