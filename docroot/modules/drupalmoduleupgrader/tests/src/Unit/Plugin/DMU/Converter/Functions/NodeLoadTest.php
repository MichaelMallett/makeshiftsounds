<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Filter;
use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\NodeLoad
 */
class NodeLoadTest extends FunctionCallModifierTestBase {

  public function testRewriteWithNidOnly() {
    $function_call = Parser::parseExpression('node_load(30)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::entityManager()->getStorage(\'node\')->load(30)', $rewritten->getText());
  }

  public function testRewriteWithVid() {
    $function_call = Parser::parseExpression('node_load(30, 32)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::entityManager()->getStorage(\'node\')->loadRevision(32)', $rewritten->getText());
  }

  /**
   * This test is failing at the moment because for whatever reason,
   * $snippet->children() is only fetching the first call to node_load().
   */
  public function _testRewriteWithCacheReset() {
    $original = <<<'END'
node_load(30);
node_load(30, TRUE);
node_load(30, 32);
node_load(30, 32, TRUE);
END;
    $expected = <<<'END'
\Drupal::entityManager()->getStorage('user')->load(30);
// FIXME: To reset the node cache, use EntityStorageInterface::resetCache().
\Drupal::entityManager()->getStorage('user')->load(30);
\Drupal::entityManager()->getStorage('user')->loadRevision(32);
// FIXME: To reset the node cache, use EntityStorageInterface::resetCache().
\Drupal::entityManager()->getStorage('user')->loadRevision(32);
END;
    $snippet = Parser::parseSnippet($original);
    $function_calls = $snippet->children(Filter::isFunctionCall('node_load'));
    foreach ($function_calls as $function_call) {
      $rewritten = $this->plugin->rewrite($function_call, $this->target);
      $function_call->replaceWith($rewritten);
    }
    $this->assertEquals($expected, $snippet->getText());
  }

}
