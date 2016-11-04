<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Analyzer;

use Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\Functions;

/**
 * @group DMU.Analyzer
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer\HookFormAlter
 */
class HookFormAlterTest extends AnalyzerTestBase {

  public function setUp() {
    parent::setUp();

    $code = <<<'END'
<?php

/**
 * Implements hook_form_alter().
 */
function foo_form_alter(array &$form, array &$form_state, $form_id) {
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function foo_form_blarg_alter(array &$form, array &$form_state) {
}
END;
    $this->dir->getChild('foo.module')->setContent($code);

    $function_indexer = new Functions([], 'function', [], $this->db, $this->target);
    $function_indexer->build();

    $this->container
      ->get('plugin.manager.drupalmoduleupgrader.indexer')
      ->method('createInstance')
      ->with('function')
      ->willReturn($function_indexer);

    $this->analyzer = $this->getPlugin();
  }

  public function testHookFormAlter() {
    $issues = $this->analyzer->analyze($this->target);
    $this->assertInternalType('array', $issues);
    $this->assertNotEmpty($issues);
    $this->assertIssueDefaults($issues[0]);
    $this->assertCount(2, $issues[0]->getViolations());
  }

  public function testDerivedFormAlter() {
    $issues = $this->analyzer->analyze($this->target);
    $this->assertInternalType('array', $issues);
    $this->assertNotEmpty($issues);
    $this->assertIssueDefaults($issues[0]);
    $this->assertCount(2, $issues[0]->getViolations());
  }

}
