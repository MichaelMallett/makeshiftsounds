<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\Define;
use Drupal\Tests\drupalmoduleupgrader\Unit\TestBase;

/**
 * @group DMU.Fixer
 */
class DefineTest extends TestBase {

  public function test() {
    $config = [
      'key' => 'foo.settings/baz',
      'value' => 'wambooli',
      'in' => '~/foo.settings.yml',
    ];
    $plugin = new Define($config, uniqID(), []);
    $plugin->setTarget($this->target);
    $plugin->execute();

    $url = $this->dir->getChild('foo.settings.yml')->url();
    $this->assertFileExists($url);
    $expected = <<<END
foo.settings:
  baz: wambooli

END;
    $this->assertEquals($expected, file_get_contents($url));
  }

}
