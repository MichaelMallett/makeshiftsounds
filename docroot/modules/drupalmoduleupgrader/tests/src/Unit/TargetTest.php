<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit;

use Drupal\drupalmoduleupgrader\Target;
use Pharborist\NodeCollection;
use Pharborist\Parser;

/**
 * @group DMU
 */
class TargetTest extends TestBase {

  /**
   * @var \Drupal\drupalmoduleupgrader\IndexerInterface
   */
  protected $indexer;

  public function setUp() {
    parent::setUp();

    $this->indexer = $this->getMockBuilder('\Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\Functions')
      ->disableOriginalConstructor()
      ->getMock();

    $this->container
      ->get('plugin.manager.drupalmoduleupgrader.indexer')
      ->method('createInstance')
      ->with('function')
      ->willReturn($this->indexer);
  }

  /**
   * @expectedException \RuntimeException
   */
  public function testInvalidBasePath() {
    // Trying to create a target with an invalid path should instantly
    // throw an exception.
    new Target('foobar', $this->container);
  }

  public function testID() {
    $this->assertEquals('foo', $this->target->id());
  }

  public function testGetBasePath() {
    $this->assertEquals($this->dir->url(), $this->target->getBasePath());
  }

  public function testGetPath() {
    $this->assertEquals($this->dir->getChild('foo.module')->url(), $this->target->getPath('.module'));
    $this->assertEquals($this->dir->getChild('foo.install')->url(), $this->target->getPath('.install'));
  }

  public function testGetFinder() {
    $this->assertInstanceOf('\Symfony\Component\Finder\Finder', $this->target->getFinder());
  }

  /**
   * @depends testGetFinder
   */
  public function testFinder() {
    $expected = $this->target->getFinder()
      ->exclude('menu_example')
      ->name('*.module')
      ->name('*.install')
      ->name('*.inc')
      ->name('*.test')
      ->name('*.php');
    $this->assertEquals(array_keys(iterator_to_array($expected)), array_keys(iterator_to_array($this->target->getFinder())));
  }

  public function testGetIndexer() {
    $this->assertSame($this->indexer, $this->target->getIndexer('function'));
  }

  public function testGetServices() {
    $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $this->target->getServices());
  }

  public function testImplementsHook() {
    $this->indexer->method('has')->willReturnMap([
      ['hook_permission', TRUE],
      ['hook_menu_alter', FALSE],
    ]);

    $this->assertTrue($this->target->implementsHook('permission'));
    $this->assertFalse($this->target->implementsHook('menu_alter'));
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testExecuteUnimplementedHook() {
    $this->indexer->method('has')->with('hook_menu')->willReturn(FALSE);
    $this->target->executeHook('menu');
  }

  public function testExecuteHook() {
    $expected = [
      'foo/baz' => [
        'title' => 'It worked!',
      ],
    ];

    $this->indexer->method('has')->with('hook_menu')->willReturn(TRUE);
    $this->indexer->method('hasExecutable')->with('hook_menu')->willReturn(TRUE);
    $this->indexer->method('execute')->with('hook_menu')->willReturn($expected);

    $actual = $this->target->executeHook('menu');
    $this->assertInternalType('array', $actual);
    $this->assertSame($expected, $actual);
  }

}
