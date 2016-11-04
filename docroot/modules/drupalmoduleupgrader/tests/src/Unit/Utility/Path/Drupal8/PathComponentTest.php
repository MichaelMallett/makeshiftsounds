<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Utility\Path\Drupal8;

use Drupal\drupalmoduleupgrader\Utility\Path\Drupal8\PathComponent;
use Drupal\Tests\UnitTestCase;

/**
 * @group DMU.Utility.Path
 */
class PathComponentTest extends UnitTestCase {

  public function testPathComponent() {
    $wildcard = new PathComponent('{node}');
    $this->assertTrue($wildcard->isWildcard());
    $this->assertEquals('{node}', $wildcard->__toString());
  }

}
