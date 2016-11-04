<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Analyzer;

use Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\Functions;

/**
 * @group DMU.Analyzer
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer\HookPermission
 *
 * @todo Add a test for dynamic permissions. Drupal 8 still uses
 * hook_permission() for this, so dynamic permissions should not result in
 * an issue being flagged.
 */
class HookPermissionTest extends AnalyzerTestBase {

  public function setUp() {
    parent::setUp();

    $code = <<<'END'
<?php

/**
 * Implements hook_permission().
 */
function foo_permission() {
  return array();
}
END;
    $this->dir->getChild('foo.module')->setContent($code);

    $indexer = new Functions([], 'function', [], $this->db, $this->target);
    $indexer->build();
    $this->container
      ->get('plugin.manager.drupalmoduleupgrader.indexer')
      ->method('createInstance')
      ->with('function')
      ->willReturn($indexer);

    $this->analyzer = $this->getPlugin();
  }

  public function test() {
    $issues = $this->analyzer->analyze($this->target);
    $this->assertInternalType('array', $issues);
    $this->assertNotEmpty($issues);
    $this->assertIssueDefaults($issues[0]);
    $this->assertCount(1, $issues[0]->getViolations());
    $fixes = $issues[0]->getFixes();
    $this->assertNotEmpty($fixes);
  }

}
