<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Analyzer;

use Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\Classes;

/**
 * @group DMU.Analyzer
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer\PSR4
 */
class PSR4Test extends AnalyzerTestBase {

  public function setUp() {
    parent::setUp();

    $code = <<<'END'
<?php

class FooBaz {}
END;
    $this->dir->getChild('foo.module')->setContent($code);

    $indexer = new Classes([], 'class', [], $this->db, $this->target);
    $indexer->build();
    $this->container
      ->get('plugin.manager.drupalmoduleupgrader.indexer')
      ->method('createInstance')
      ->with('class')
      ->willReturn($indexer);

    $this->analyzer = $this->getPlugin();
  }

  public function test() {
    $issues = $this->analyzer->analyze($this->target);
    $this->assertInternalType('array', $issues);
    $this->assertNotEmpty($issues);
    $this->assertIssueDefaults($issues[0]);
  }

}
