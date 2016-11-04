<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Indexer;
use Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\Classes;

/**
 * @group DMU.Indexer
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\Classes
 *
 * @expectID Foobaz
 * @expectType \Pharborist\Objects\ClassNode
 */
class ClassesTest extends IndexerTestBase {

  public function setUp() {
    parent::setUp();

    $code = <<<'END'
<?php

class Foobaz {}
END;
    $this->dir->getChild('foo.module')->setContent($code);

    $this->indexer = new Classes([], 'class', [], $this->db, $this->target);
    $this->indexer->build();
  }

}
