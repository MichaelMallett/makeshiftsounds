<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit;

use Drupal\drupalmoduleupgrader\Issue;
use Drupal\drupalmoduleupgrader\Report;

/**
 * @group DMU
 */
class ReportTest extends TestBase {

  /**
   * @var \Drupal\drupalmoduleupgrader\ReportInterface
   */
  private $report;

  public function setUp() {
    parent::setUp();
    $this->report = new Report();
  }

  public function test() {
    $issue = new Issue($this->target, 'Foo');
    $this->report->addIssue($issue);

    $issue = new Issue($this->target, 'Baz');
    $this->report->addIssue($issue);

    $issues = $this->report->getIssues();
    $this->assertTrue(is_array($issues));
    $this->assertCount(2, $issues);
    $this->assertInstanceOf('\Drupal\drupalmoduleupgrader\IssueInterface', $issues[0]);
    $this->assertEquals('Foo', $issues[0]->getTitle());
    $this->assertInstanceOf('\Drupal\drupalmoduleupgrader\IssueInterface', $issues[1]);
    $this->assertEquals('Baz', $issues[1]->getTitle());
  }

}
