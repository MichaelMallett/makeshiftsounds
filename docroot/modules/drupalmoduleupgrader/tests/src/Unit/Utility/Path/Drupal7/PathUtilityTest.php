<?php

/**
 * @file
 * Contains \Drupal\Tests\drupalmoduleupgrader\Unit\Utility\Path\Drupal7\PathUtilityTest.
 */

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Utility\Path\Drupal7;

use Drupal\drupalmoduleupgrader\Utility\Path\Drupal7\PathComponent;
use Drupal\drupalmoduleupgrader\Utility\Path\Drupal7\PathUtility;
use Drupal\Tests\UnitTestCase;

/**
 * @group DMU.Utility.Path
 */
class PathUtilityTest extends UnitTestCase {

  public function __construct() {
    $this->path = new PathUtility('node/%node/foo/%');
  }

  public function testCount() {
    $this->assertCount(4, $this->path);
  }

  public function testAdd() {
    $path = clone $this->path;

    $path->add('baz');
    $this->assertCount(5, $path);
    $this->assertInstanceOf('Drupal\\drupalmoduleupgrader\\Utility\\Path\\Drupal7\\PathComponent', $path->last());
    $this->assertEquals('baz', $path->last()->__toString());

    $path->add(new PathComponent('wambooli'));
    $this->assertCount(6, $path);
    $this->assertEquals('wambooli', $path->last()->__toString());
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testAddArray() {
    $this->path->add([]);
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testAddObject() {
    $this->path->add(new \StdClass());
  }

  public function testFind() {
    $result = $this->path->find('foo');
    $this->assertCount(1, $result);
    $this->assertInstanceOf('Drupal\\drupalmoduleupgrader\\Utility\\Path\\Drupal7\\PathComponent', $result->first());
    $this->assertEquals('foo', $result->first()->__toString());
  }

  public function testContains() {
    $this->assertTrue($this->path->contains('%node'));
    $this->assertFalse($this->path->contains('fruit'));
  }

  public function testHasWildcards() {
    $this->assertTrue($this->path->hasWildcards());
  }

  public function testGetWildcards() {
    $this->assertEquals('%node', $this->path->getWildcards()->__toString());
  }

  public function testGetNextWildcard() {
    $wildcard = $this->path->getNextWildcard();
    $this->assertInstanceOf('Drupal\\drupalmoduleupgrader\\Utility\\Path\\Drupal7\\PathComponent', $wildcard);
    $this->assertEquals('%node', $wildcard->__toString());

    $wildcard = $this->path->getNextWildcard();
    $this->assertNull($wildcard);
  }

  public function testDeleteWildcards() {
    $this->assertEquals('node/foo/%', $this->path->deleteWildcards()->__toString());
  }

  public function testGetParent() {
    $this->assertEquals('node/%node/foo', $this->path->getParent()->__toString());
  }

  public function testIsDynamic() {
    $this->assertTrue($this->path->isDynamic());
  }

  public function testHasPlaceholders() {
    $this->assertTrue($this->path->hasPlaceholders());
  }

  public function testGetPlaceholders() {
    $placeholders = $this->path->getPlaceholders();
    $this->assertCount(1, $placeholders);
    $this->assertInstanceOf('Drupal\\drupalmoduleupgrader\\Utility\\Path\\Drupal7\\PathComponent', $placeholders->first());
    $this->assertEquals('%', $placeholders->first()->__toString());
  }

  /**
   * @depends testHasPlaceholders
   */
  public function testDeletePlaceholders() {
    $this->assertFalse($this->path->deletePlaceholders()->hasPlaceholders());
  }

}
