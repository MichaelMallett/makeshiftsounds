<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Analyzer;

/**
 * @group DMU.Analyzer
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer\InfoFile
 */
class InfoFileTest extends AnalyzerTestBase {

  public function setUp() {
    parent::setUp();

    $info = <<<'END'
name = "Foobar"
core = "7.x"
files[] = foo.test
END;
    $this->dir->getChild('foo.info')->setContent($info);

    $this->analyzer = $this->getPlugin([], [
      'documentation' => [
        [ 'url' => 'http://www.google.com', 'title' => 'Google it, baby.' ],
      ],
    ]);
  }

  public function test() {
    $issues = $this->analyzer->analyze($this->target);
    $this->assertInternalType('array', $issues);
    $this->assertArrayHasKey('core', $issues);
    $this->assertArrayHasKey('type', $issues);
    $this->assertArrayNotHasKey('dependencies', $issues);
    $this->assertArrayHasKey('files', $issues);
    $this->assertArrayNotHasKey('configure', $issues);
  }

}
