<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Indexer;
use Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\Functions;

/**
 * @group DMU.Indexer
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\Functions
 *
 * @expectID foo_blarg
 * @expectType \Pharborist\Functions\FunctionDeclarationNode
 */
class FunctionsTest extends IndexerTestBase {

  public function setUp() {
    parent::setUp();

    $code = <<<'END'
<?php

function foo_blarg() {}
END;
    $this->dir->getChild('foo.module')->setContent($code);

    $this->indexer = new Functions([], 'function', [], $this->db, $this->target);
    $this->indexer->build();
  }

  public function testQuery() {
    $this->assertInstanceOf('\Drupal\Core\Database\Query\Select', $this->indexer->getQuery());
  }

}
