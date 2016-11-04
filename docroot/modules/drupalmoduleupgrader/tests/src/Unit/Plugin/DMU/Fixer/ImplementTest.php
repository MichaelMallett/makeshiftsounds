<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\IOException;
use Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\Implement;
use Drupal\Tests\drupalmoduleupgrader\Unit\TestBase;
use Pharborist\NodeCollection;
use Pharborist\Objects\ClassNode;

/**
 * @group DMU.Fixer
 */
class ImplementTest extends TestBase {

  public function test() {
    $class = ClassNode::create('Foobaz');
    $indexer = $this->getMock('\Drupal\drupalmoduleupgrader\IndexerInterface');
    $indexer->method('get')->with('Foobaz')->willReturn(new NodeCollection([$class]));

    $this
      ->container
      ->get('plugin.manager.drupalmoduleupgrader.indexer')
      ->method('createInstance')
      ->with('class')
      ->willReturn($indexer);

    $config = [
      'definition' => '\Drupal\Core\Block\BlockPluginInterface::blockForm',
      'target' => 'Foobaz',
    ];
    $plugin = new Implement($config, uniqID(), []);
    $plugin->setTarget($this->target);
    try {
      // We expect a CodeManagerIOException because we're implementing the
      // method on a class that is not officially part of the target's code.
      // That's OK, though.
      $plugin->execute();
    }
    catch (IOException $e) {}

    $this->assertTrue($class->hasMethod('blockForm'));
    $method = $class->getMethod('blockForm');
    $this->assertInstanceOf('\Pharborist\Objects\ClassMethodNode', $method);
    $parameters = $method->getParameters();
    $this->assertCount(2, $parameters);
    $this->assertEquals($parameters[0]->getName(), 'form');
    $this->assertNull($parameters[0]->getTypeHint());
    $this->assertEquals($parameters[1]->getName(), 'form_state');
    $type = $parameters[1]->getTypeHint();
    $this->assertInstanceOf('\Pharborist\Namespaces\NameNode', $type);
    $this->assertEquals('Drupal\Core\Form\FormStateInterface', $type->getText());
  }

}
