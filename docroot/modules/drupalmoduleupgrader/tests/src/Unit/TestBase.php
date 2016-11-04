<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit;

use Drupal\drupalmoduleupgrader\Target;
use Drupal\Tests\UnitTestCase;

/**
 * Base class for all DMU tests, providing a useful environment:
 *
 * - A module called foo, mocked in memory using vfsStream. The actual
 *   module files are empty and should be filled in by subclasses.
 * - A TargetInterface instance for the foo module.
 * - A Drupal database connection to an empty in-memory SQLite database.
 * - A container with mocked string_translation and logger.factory services.
 */
abstract class TestBase extends UnitTestCase {

  use ContainerMockTrait;
  use SQLiteDatabaseTrait;
  use ModuleMockerTrait;

  /**
   * The parsed annotations for the test class and method being executed.
   *
   * @var array
   */
  protected $info;

  /**
   * @var \org\bovigo\vfs\vfsStreamDirectory
   */
  protected $dir;

  /**
   * @var \Drupal\drupalmoduleupgrader\TargetInterface
   */
  protected $target;

  /**
   * Mocks an entire module, called foo, in a virtual file system.
   */
  public function setUp() {
    $this->info = $this->getAnnotations();

    $this->dir = $this->mockModule('foo');

    $this->mockContainer();
    $this->mockTranslator();
    $this->mockLogger();
    $this->initDB();

    // At the time of this writing, Target will pull the indexer manager out
    // of the container right away, so let's mock it.
    $indexers = $this->getMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $this->container->set('plugin.manager.drupalmoduleupgrader.indexer', $indexers);

    $this->target = new Target($this->dir->url(), $this->container);
  }

  /**
   * Instantiates the plugin class covered by this test (as indicated by the
   * @covers annotation). The plugin instance is given a randomly generated
   * ID and description. Dependencies will be pulled from $this->container,
   * so this should only be called once the mock container is ready.
   *
   * @param array $configuration
   *  Additional configuration to pass to the instance.
   * @param array $plugin_definition
   *  Additional definition info to pass to the instance.
   *
   * @return object
   *  A plugin instance.
   */
  protected function getPlugin(array $configuration = [], $plugin_definition = []) {
    $plugin_definition['description'] = $this->getRandomGenerator()->sentences(4);

    $class = $this->info['class']['covers'][0];
    return $class::create($this->container, $configuration, $this->randomMachineName(), $plugin_definition);
  }

}
