<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\PSR4;
use Drupal\Tests\drupalmoduleupgrader\Unit\TestBase;
use org\bovigo\vfs\vfsStream;
use Pharborist\NodeCollection;
use Pharborist\Objects\ClassNode;

/**
 * @group DMU.Fixer
 */
class PSR4Test extends TestBase {

  public function test() {
    $class = ClassNode::create('Wambooli');
    $indexer = $this->getMock('\Drupal\drupalmoduleupgrader\IndexerInterface');
    $indexer->method('get')->with('Wambooli')->willReturn(new NodeCollection([ $class ]));

    $this
      ->container
      ->get('plugin.manager.drupalmoduleupgrader.indexer')
      ->method('createInstance')
      ->with('class')
      ->willReturn($indexer);

    $config = [
      'source' => 'Wambooli',
      'destination' => 'Drupal\foo\Wambooli',
    ];
    $plugin = new PSR4($config, uniqID(), []);
    $plugin->setTarget($this->target);
    $plugin->execute();

    $url = $this->target->getPath('src/Wambooli.php');
    $this->assertFileExists($url);
  }

}
