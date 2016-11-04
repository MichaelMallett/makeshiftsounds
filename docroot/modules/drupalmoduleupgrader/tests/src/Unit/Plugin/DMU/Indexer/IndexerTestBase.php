<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Indexer;

use Drupal\Tests\drupalmoduleupgrader\Unit\TestBase;

/**
 * Base class for tests of DMU's indexer plugins. Because the indexers'
 * behavior is so standard, this class reflects that by implementing a lot
 * of standard assertions.
 */
abstract class IndexerTestBase extends TestBase {

  /**
   * @var \Drupal\drupalmoduleupgrader\IndexerInterface
   */
  protected $indexer;

  public function testClear() {
    $this->indexer->clear();
    $this->assertCount(0, $this->indexer);
  }

  public function testHas() {
    $this->assertTrue($this->indexer->has($this->info['class']['expectID'][0]));
    $this->assertFalse($this->indexer->has(uniqID()));
  }

  public function testGet() {
    $node = $this->indexer->get($this->info['class']['expectID'][0]);

    $this->assertFalse($collection->isEmpty());

    $this->assertInstanceOf($this->info['class']['expectType'][0], $node);
  }

  /**
   * @depends testHas
   */
  public function testDelete() {
    $id = $this->info['class']['expectID'][0];
    $this->indexer->delete($id);
    $this->assertFalse($this->indexer->has($id));
  }

}
