<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Analyzer;

use Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\FunctionCalls;

/**
 * @group DMU.Analyzer
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer\FunctionCall
 */
class FunctionCallTest extends AnalyzerTestBase {

  public function setUp() {
    parent::setUp();

    $code = <<<'END'
<?php

function foo_blorf() {
  $data = array();
  drupal_write_record($data, 'id');
}
END;
    $this->dir->getChild('foo.module')->setContent($code);

    $indexer = new FunctionCalls([], 'function', [], $this->db, $this->target);
    $indexer->build();
    $this->container
      ->get('plugin.manager.drupalmoduleupgrader.indexer')
      ->method('createInstance')
      ->with('function_call')
      ->willReturn($indexer);

    $this->analyzer = $this->getPlugin([], ['function' => 'drupal_write_record']);
  }

  public function test() {
    $issues = $this->analyzer->analyze($this->target);
    $this->assertInternalType('array', $issues);
    $this->assertNotEmpty($issues);
    $this->assertIssueDefaults($issues[0]);
    $this->assertCount(1, $issues[0]->getViolations());
  }

}
