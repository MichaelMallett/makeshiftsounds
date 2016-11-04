<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Fixer;

use Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\Disable;
use Drupal\drupalmoduleupgrader\Plugin\DMU\Indexer\FunctionCalls;
use Drupal\Tests\drupalmoduleupgrader\Unit\TestBase;

/**
 * @group DMU.Fixer
 *
 * @TODO Add a test of the 'where' configuration option.
 */
class DisableTest extends TestBase {

  public function test() {
    $code = <<<'END'
<?php

variable_get('foo');
END;
    $this->dir->getChild('foo.module')->setContent(rtrim($code));

    $indexer = new FunctionCalls([], 'function', ['exclude' => []], $this->db, $this->target);
    $indexer->build();

    $this
      ->container
      ->get('plugin.manager.drupalmoduleupgrader.indexer')
      ->method('createInstance')
      ->with('function_call')
      ->willReturn($indexer);

    $config = [
      'type' => 'function_call',
      'id' => 'variable_get',
      'note' => 'This is no longer kosher!',
    ];
    $plugin = new Disable($config, uniqID(), []);
    $plugin->setTarget($this->target);
    $plugin->execute();

    $expected = <<<END
<?php

// This is no longer kosher!
// variable_get('foo');
END;

    // trim() makes the test pass. Go figure.
    $this->assertEquals($expected, trim($this->dir->getChild('foo.module')->getContent()));
  }

}
