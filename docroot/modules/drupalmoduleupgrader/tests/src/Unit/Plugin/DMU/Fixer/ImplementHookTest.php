<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\ImplementHook;
use Drupal\Tests\drupalmoduleupgrader\Unit\TestBase;
use Pharborist\Filter;

/**
 * @group DMU.Fixer
 */
class ImplementHookTest extends TestBase {

  public function test() {
    eval('function hook_tokens($type, $tokens, array $data = array(), array $options = array()) {}');

    $config = [
      'hook' => 'tokens',
      'module' => 'system',
    ];
    $module_handler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');
    $plugin = new ImplementHook($config, uniqID(), [], $module_handler);
    $plugin->setTarget($this->target);
    $plugin->execute();

    $module = $this->target->getPath('.module');
    $function = $this->target->open($module)->children(Filter::isFunction('foo_tokens'))->get(0);
    $this->assertInstanceOf('\Pharborist\Functions\FunctionDeclarationNode', $function);
    $this->assertEquals('foo_tokens', $function->getName()->getText());

    $parameters = $function->getParameters();
    $this->assertCount(4, $parameters);

    $this->assertNull($parameters[0]->getTypeHint());
    $this->assertEquals('type', $parameters[0]->getName());
    $this->assertNull($parameters[0]->getValue());

    $this->assertNull($parameters[1]->getTypeHint());
    $this->assertEquals('tokens', $parameters[1]->getName());
    $this->assertNull($parameters[1]->getValue());

    $this->assertInstanceOf('\Pharborist\TokenNode', $parameters[2]->getTypeHint());
    $this->assertSame(T_ARRAY, $parameters[2]->getTypeHint()->getType());
    $this->assertEquals('data', $parameters[2]->getName());
    $this->assertInstanceOf('\Pharborist\Types\ArrayNode', $parameters[2]->getValue());

    $this->assertInstanceOf('\Pharborist\TokenNode', $parameters[3]->getTypeHint());
    $this->assertSame(T_ARRAY, $parameters[3]->getTypeHint()->getType());
    $this->assertEquals('options', $parameters[3]->getName());
    $this->assertInstanceOf('\Pharborist\Types\ArrayNode', $parameters[3]->getValue());
  }

}
