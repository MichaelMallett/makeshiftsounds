<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\DB
 *
 * Currently, the DB plugin behaves identically for every function it handles,
 * so I'm only bothering to test db_select().
 */
class DBTest extends FunctionCallModifierTestBase {

  public function setUp() {
    parent::setUp();
    $this->plugin = $this->getPlugin([], [ 'function' => 'db_select' ]);
  }

  public function testRewriteDBSelectAllowedTable() {
    $function_call = Parser::parseExpression('db_select("session")');
    $this->assertSame($function_call, $this->plugin->rewrite($function_call, $this->target));
  }

  public function testRewriteDBSelectForbiddenTable() {
    $function_call = Parser::parseExpression('db_select("variable")');
    $this->assertNull($this->plugin->rewrite($function_call, $this->target));
  }

}
