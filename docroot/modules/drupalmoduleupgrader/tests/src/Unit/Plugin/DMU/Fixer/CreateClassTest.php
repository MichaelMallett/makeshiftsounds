<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\CreateClass;
use Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\Delete;
use Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\Classes;
use Drupal\Tests\drupalmoduleupgrader\Unit\TestBase;

/**
 * @group DMU.Fixer
 */
class CreateClassTest extends TestBase {

  public function test() {
    $indexer = new Classes([], 'class', [], $this->db, $this->target);
    $indexer->build();

    $this
      ->container
      ->get('plugin.manager.drupalmoduleupgrader.indexer')
      ->method('createInstance')
      ->with('class')
      ->willReturn($indexer);

    $config = [
      'class' => '\Drupal\foo\MyBaz',
      'destination' => '~/src/MyBaz.php',
      'parent' => '\Drupal\Core\Baz\BazBase',
      'interfaces' => [
        '\Drupal\Core\Baz\BazInterface',
        '\Drupal\Core\Executable\ExecutableInterface',
      ],
      'doc' => 'This is my bazzifier. There are many like it, but this one is mine.',
    ];
    $plugin = new CreateClass($config, uniqID(), []);
    $plugin->setTarget($this->target);
    $plugin->execute();

    $this->assertTrue($indexer->has('MyBaz'));
    $classes = $indexer->get('MyBaz');
    $this->assertCount(1, $classes);
    /** @var \Pharborist\Objects\ClassNode $class */
    $class = $classes->get(0);
    $this->assertInstanceOf('\Pharborist\Objects\ClassNode', $class);
    $this->assertEquals('\Drupal\foo\MyBaz', $class->getName()->getAbsolutePath());
    $this->assertEquals('MyBaz', $class->getName()->getText());
    $parent = $class->getExtends();
    $this->assertInstanceOf('\Pharborist\Namespaces\NameNode', $parent);
    $this->assertEquals('BazBase', $parent->getText());
    return;
    $interfaces = $class->getImplementList();
    $this->assertCount(2, $interfaces->getItems());
    $this->assertEquals('BazInterface', $interfaces->get(0)->getText());
    $this->assertEquals('ExecutableInterface', $interfaces->get(1)->getText());
  }

}
