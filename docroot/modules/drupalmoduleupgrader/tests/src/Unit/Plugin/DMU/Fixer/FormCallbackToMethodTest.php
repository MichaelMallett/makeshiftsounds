<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\IOException;
use Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\FormCallbackToMethod;
use Drupal\Tests\drupalmoduleupgrader\Unit\TestBase;
use Pharborist\NodeCollection;
use Pharborist\Objects\ClassNode;
use Pharborist\Parser;

/**
 * @group DMU.Fixer
 */
class FormCallbackToMethodTest extends TestBase {

  public function test() {
    $callback = Parser::parseSnippet('function foo_submit(&$form, &$form_state) {}');
    $function_indexer = $this->getMock('\Drupal\drupalmoduleupgrader\IndexerInterface');
    $function_indexer->method('get')->with('foo_submit')->willReturn(new NodeCollection([ $callback ]));

    $class = ClassNode::create('FooForm');
    $class_indexer = $this->getMock('\Drupal\drupalmoduleupgrader\IndexerInterface');
    $class_indexer->method('get')->with('FooForm')->willReturn(new NodeCollection([ $class ]));

    $this
      ->container
      ->get('plugin.manager.drupalmoduleupgrader.indexer')
      ->method('createInstance')
      ->willReturnCallback(function($which) use ($class_indexer, $function_indexer) {
        switch ($which) {
          case 'class':
            return $class_indexer;
          case 'function':
            return $function_indexer;
          default:
            break;
        }
      });

    $config = [
      'callback' => 'foo_submit',
      'destination' => 'FooForm::submitForm',
    ];
    $plugin = new FormCallbackToMethod($config, uniqID(), []);
    $plugin->setTarget($this->target);
    try {
      // We expect a CodeManagerIOException because we're implementing the
      // method on a class that is not officially part of the target's code.
      // That's OK, though.
      $plugin->execute();
    }
    catch (IOException $e) {}

    $this->assertTrue($class->hasMethod('submitForm'));
    $parameters = $class->getMethod('submitForm')->getParameters();
    $this->assertCount(2, $parameters);
    $this->assertEquals('form', $parameters[0]->getName());
    $this->assertInstanceOf('\Pharborist\TokenNode', $parameters[0]->getTypeHint());
    $this->assertSame(T_ARRAY, $parameters[0]->getTypeHint()->getType());
    $this->assertInstanceOf('\Pharborist\TokenNode', $parameters[0]->getReference());
    $this->assertEquals('form_state', $parameters[1]->getName());
    $this->assertInstanceOf('\Pharborist\Namespaces\NameNode', $parameters[1]->getTypeHint());
    $this->assertEquals('Drupal\Core\Form\FormStateInterface', $parameters[1]->getTypeHint()->getText());
    $this->assertNull($parameters[1]->getReference());
  }

}
