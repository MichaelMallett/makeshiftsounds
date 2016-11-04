<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Fixer;

use Drupal\Component\Serialization\Yaml as YAML;
use Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\HookToYAML;
use Drupal\Tests\drupalmoduleupgrader\Unit\TestBase;

/**
 * @group DMU.Fixer
 */
class HookToYAMLTest extends TestBase {

  public function test() {
    $permissions = [
      'bazify' => [
        'title' => 'Do snazzy bazzy things',
      ],
    ];

    $indexer = $this->getMockBuilder('\Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\Functions')
      ->disableOriginalConstructor()
      ->getMock();
    $indexer->method('has')->with('hook_permission')->willReturn(TRUE);
    $indexer->method('hasExecutable')->with('hook_permission')->willReturn(TRUE);
    $indexer->method('execute')->with('hook_permission')->willReturn($permissions);
    $this
      ->container
      ->get('plugin.manager.drupalmoduleupgrader.indexer')
      ->method('createInstance')
      ->with('function')
      ->willReturn($indexer);

    $config = [
      'hook' => 'permission',
      'destination' => '~/foo.permissions.yml',
    ];
    $plugin = new HookToYAML($config, uniqID(), []);
    $plugin->setTarget($this->target);
    $plugin->execute();

    $url = $this->dir->getChild('foo.permissions.yml')->url();
    $this->assertFileExists($url);
    $this->assertSame(YAML::encode($permissions), file_get_contents($url));
  }

}
