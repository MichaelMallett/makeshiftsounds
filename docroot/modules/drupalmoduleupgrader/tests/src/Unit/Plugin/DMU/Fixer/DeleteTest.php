<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\Delete;
use Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\Functions;
use Drupal\Tests\drupalmoduleupgrader\Unit\TestBase;

/**
 * @group DMU.Fixer
 *
 * @TODO Add a test of the 'where' configuration option.
 */
class
DeleteTest extends TestBase {

  public function test() {
    $code = <<<'END'
<?php

function foo_permission() {
  return array(
    'bazify' => array(
      'title' => 'Do snazzy bazzy things',
    ),
  );
}
END;
    $this->dir->getChild('foo.module')->setContent($code);

    $indexer = new Functions([], 'function', [], $this->db, $this->target);
    $indexer->build();

    $this
      ->container
      ->get('plugin.manager.drupalmoduleupgrader.indexer')
      ->method('createInstance')
      ->with('function')
      ->willReturn($indexer);

    $config = [
      'type' => 'function',
      'id' => 'hook_permission',
    ];
    $plugin = new Delete($config, uniqid(), []);
    $plugin->setTarget($this->target);
    $plugin->execute();

    $this->assertFalse($indexer->has('permission'));
    $this->assertEquals("<?php\n\n", $this->dir->getChild('foo.module')->getContent());
  }

}
