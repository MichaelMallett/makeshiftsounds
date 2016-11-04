<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit;

use Drupal\drupalmoduleupgrader\Issue;
use Pharborist\Filter;

/**
 * @group DMU
 */
class IssueTest extends TestBase {

  /**
   * @var \Drupal\drupalmoduleupgrader\IssueInterface
   */
  private $issue;

  public function setUp() {
    parent::setUp();
    $this->issue = new Issue($this->target, 'Foobaz');
  }

  public function testTitle() {
    $this->issue->setTitle('Foobar');
    $this->assertEquals('Foobar', $this->issue->getTitle());
  }

  public function testSummary() {
    $this->issue->setSummary('Lorem ipsum dolor sit amet, consectetuer adipiscing elit.');
    $this->assertEquals("<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</p>\n", $this->issue->getSummary());
  }

  public function testDocumentation() {
    $this->issue->addDocumentation('http://www.google.com', 'Just Google it, baby!');
    $documentation = $this->issue->getDocumentation();
    $this->assertInternalType('array', $documentation);
    $this->assertCount(1, $documentation);
    $this->assertArrayHasKey('url', $documentation[0]);
    $this->assertArrayHasKey('title', $documentation[0]);
    $this->assertEquals('http://www.google.com', $documentation[0]['url']);
    $this->assertEquals('Just Google it, baby!', $documentation[0]['title']);
  }

  public function testViolationsAndDetectors() {
    $analyzer = $this->getMockBuilder('\Drupal\drupalmoduleupgrader\AnalyzerBase')->disableOriginalConstructor()->getMock();
    $analyzer->method('getPluginId')->willReturn('blarg');
    $this->issue->addAffectedFile($this->dir->getChild('foo.info')->url(), $analyzer);

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

    $node = $this->target
      ->open($this->dir->getChild('foo.module')->url())
      ->children(Filter::isFunction('foo_permission'))
      ->get(0);
    $this->issue->addViolation($node, $analyzer);

    $violations = $this->issue->getViolations();
    $this->assertInternalType('array', $violations);
    $this->assertCount(2, $violations);
    $this->assertArrayHasKey('file', $violations[0]);
    $this->assertArrayNotHasKey('line_number', $violations[0]);
    $this->assertEquals($this->dir->getChild('foo.info')->url(), $violations[0]['file']);
    $this->assertArrayHasKey('file', $violations[1]);
    $this->assertArrayHasKey('line_number', $violations[1]);
    $this->assertEquals($this->dir->getChild('foo.module')->url(), $violations[1]['file']);

    $detectors = $this->issue->getDetectors();
    $this->assertInternalType('array', $detectors);
    $this->assertCount(1, $detectors);
    $this->assertEquals($analyzer->getPluginId(), $detectors[0]);
  }

  public function testFixes() {
    $this->issue->addFix('foo');
    $this->issue->addFix('baz', ['bar' => 'wambooli']);

    $fixes = $this->issue->getFixes();
    $this->assertInternalType('array', $fixes);
    $this->assertCount(2, $fixes);
    $this->assertEquals(['_plugin_id' => 'foo'], $fixes[0]);
    $this->assertEquals(['_plugin_id' => 'baz', 'bar' => 'wambooli'], $fixes[1]);
  }

}
