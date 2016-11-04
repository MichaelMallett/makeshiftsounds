<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\Notify;
use Drupal\Tests\drupalmoduleupgrader\Unit\TestBase;
use Pharborist\DocCommentNode;
use Pharborist\NodeCollection;
use Pharborist\Objects\ClassNode;

/**
 * @group DMU.Fixer
 *
 * @TODO Add a test of the 'where' configuration option.
 */
class NotifyTest extends TestBase {

  public function testDocComment() {
    $class = ClassNode::create('Wambooli');
    $class->setDocComment(DocCommentNode::create('Double wambooli!'));
    $this->assertInstanceOf('\Pharborist\DocCommentNode', $class->getDocComment());
    $indexer = $this->getMock('\Drupal\drupalmoduleupgrader\IndexerInterface');
    $indexer->method('get')->with('Wambooli')->willReturn(new NodeCollection([ $class ]));

    $this
      ->container
      ->get('plugin.manager.drupalmoduleupgrader.indexer')
      ->method('createInstance')
      ->with('class')
      ->willReturn($indexer);

    $config = [
      'type' => 'class',
      'id' => 'Wambooli',
      'note' => 'You need to rewrite this thing because I said so!',
    ];
    $plugin = new Notify($config, uniqID(), []);
    $plugin->setTarget($this->target);
    $plugin->execute();

    $comment = $class->getDocComment();
    $this->assertInstanceOf('\Pharborist\DocCommentNode', $comment);
    $expected = <<<END
Double wambooli!

You need to rewrite this thing because I said so!
END;
    $this->assertEquals($expected, $comment->getCommentText());
  }

}
