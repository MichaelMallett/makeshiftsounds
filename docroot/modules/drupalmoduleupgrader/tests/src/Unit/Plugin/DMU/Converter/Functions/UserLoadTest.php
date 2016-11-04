<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Filter;
use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\UserLoad
 */
class UserLoadTest extends FunctionCallModifierTestBase {

  public function testRewriteWithoutCacheReset() {
    $function_call = Parser::parseExpression('user_load(30)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::entityManager()->getStorage(\'user\')->load(30)', $rewritten->getText());
  }

  public function testRewriteWithCacheReset() {
    $original = <<<'END'
user_load(30, TRUE);
END;
    $expected = <<<'END'
// @FIXME
// To reset the user cache, use EntityStorageInterface::resetCache().
\Drupal::entityManager()->getStorage('user')->load(30);
END;
    $snippet = Parser::parseSnippet($original);
    $function_call = $snippet->children(Filter::isFunctionCall('user_load'))->get(0);
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $function_call->replaceWith($rewritten);
    $this->assertEquals($expected, $snippet->getText());
  }

}
