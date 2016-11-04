<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Routing;

use Drupal\drupalmoduleupgrader\Routing\ParameterBinding;
use Drupal\drupalmoduleupgrader\Utility\Path\Drupal7\PathUtility;
use Drupal\Tests\UnitTestCase;
use Pharborist\Functions\ParameterNode;
use Pharborist\Types\StringNode;

/**
 * @group DMU.Routing
 */
class ParameterBindingTest extends UnitTestCase {

  /**
   * @var ParameterNode
   */
  private $parameter;

  public function setUp() {
    // ParameterNode supports variadic parameters, which use the T_ELLIPSIS
    // token. Which will be undefined on any PHP older than 5.6. So this kludges
    // around that.
    if (! defined('T_ELLIPSIS')) {
      define('T_ELLIPSIS', 0);
    }

    $this->parameter = ParameterNode::create('foo');
  }

  public function testGetParameter() {
    $path = new PathUtility('foo/baz');
    $binding = new ParameterBinding($path, $this->parameter);
    $this->assertSame($this->parameter, $binding->getParameter());
  }

  public function testInPath() {
    $path = new PathUtility('foo/baz');
    $binding = new ParameterBinding($path, $this->parameter);
    $this->assertFalse($binding->inPath());

    $path = new PathUtility('foo/%node');
    $binding = new ParameterBinding($path, $this->parameter, 1);
    $this->assertTrue($binding->inPath());
  }

  public function testHasArgument() {
    $path = new PathUtility('foo/baz');
    $binding = new ParameterBinding($path, $this->parameter);
    $this->assertFalse($binding->hasArgument());

    $path = new PathUtility('foo/%node');
    $binding = new ParameterBinding($path, $this->parameter, 1);
    $this->assertTrue($binding->hasArgument());

    $path = new PathUtility('foo/%');
    $binding = new ParameterBinding($path, $this->parameter, 'baz');
    $this->assertTrue($binding->hasArgument());
  }

  public function testGetArgument() {
    $path = new PathUtility('foo/%node');
    $binding = new ParameterBinding($path, $this->parameter, 1);
    $this->assertSame(1, $binding->getArgument());

    $path = new PathUtility('foo/%');
    $binding = new ParameterBinding($path, $this->parameter, 'baz');
    $this->assertEquals('baz', $binding->getArgument());
  }

  public function testIsPathPosition() {
    $path = new PathUtility('foo/%node');
    $binding = new ParameterBinding($path, $this->parameter, 1);
    $this->assertTrue($binding->isPathPosition());

    $path = new PathUtility('foo/%');
    $binding = new ParameterBinding($path, $this->parameter, 'baz');
    $this->assertFalse($binding->isPathPosition());
  }

  public function testGetValuePathPositionInPath() {
    $path = new PathUtility('foo/%node');
    $binding = new ParameterBinding($path, $this->parameter, 1);
    $value = $binding->getValue();
    $this->assertInstanceOf('\Drupal\drupalmoduleupgrader\Utility\Path\PathComponentInterface', $value);
    $this->assertEquals('%node', $value);
  }

  public function testGetValuePathPositionNotInPath() {
    $path = new PathUtility('foo/%node');
    $binding = new ParameterBinding($path, $this->parameter, 2);
    $value = $binding->getValue();
    $this->assertInstanceOf('\Drupal\drupalmoduleupgrader\Utility\Path\PathComponentInterface', $value);
    $this->assertEquals('%', $value);
  }

  public function testGetValueStaticArgument() {
    $path = new PathUtility('foo/%node');
    $binding = new ParameterBinding($path, $this->parameter, 'baz');
    $this->assertEquals('baz', $binding->getValue());
  }

  public function testGetValueNoArgument() {
    $this->parameter->setValue(StringNode::fromValue('har'));
    $path = new PathUtility('foo/%node');
    $binding = new ParameterBinding($path, $this->parameter);
    $this->assertEquals('har', $binding->getValue());
  }

  public function testGetValueNoArgumentNoDefaultvalue() {
    $path = new PathUtility('foo/%node');
    $binding = new ParameterBinding($path, $this->parameter);
    $this->assertNull($binding->getValue());
  }

}
