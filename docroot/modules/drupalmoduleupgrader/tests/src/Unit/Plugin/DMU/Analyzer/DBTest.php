<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Analyzer;

use Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\FunctionCalls;

/**
 * @group DMU.Analyzer
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer\DB
 */
class DBTest extends AnalyzerTestBase {

  public function setUp() {
    parent::setUp();

    $code = <<<'END'
<?php

/**
 * Implements hook_uninstall().
 */
function foo_uninstall() {
  db_delete('variable')->condition('name', 'foo_baz')->execute();
}
END;
    $this->dir->getChild('foo.install')->setContent($code);

    $indexer = new FunctionCalls([], 'function', [], $this->db, $this->target);
    $indexer->build();
    $this->container
      ->get('plugin.manager.drupalmoduleupgrader.indexer')
      ->method('createInstance')
      ->with('function_call')
      ->willReturn($indexer);

    $this->analyzer = $this->getPlugin([], ['function' => 'db_delete']);
  }

  public function test() {
    $issues = $this->analyzer->analyze($this->target);
    $this->assertInternalType('array', $issues);
    $this->assertNotEmpty($issues);
    $this->assertIssueDefaults($issues[0]);
    $this->assertCount(1, $issues[0]->getViolations());
  }

}
