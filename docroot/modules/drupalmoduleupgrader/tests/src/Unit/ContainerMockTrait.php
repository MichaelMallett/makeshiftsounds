<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * A trait for tests that need a mock container; contains (deprecated) methods
 * to mock basic translation and logging services as well.
 */
trait ContainerMockTrait {

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  protected function mockContainer() {
    if (empty($this->container)) {
      // Using a ContainerBuilder lets us simply stick services into the
      // container, which is a whole lot easier than mocking it!
      $this->container = new ContainerBuilder();
    }
  }

  protected function mockTranslator() {
    $this->mockContainer();

    // Mock the string_translation service; calling its translate()
    // method will return the original, unprocessed string.
    $translator = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');
    $translator->method('translate')->willReturnArgument(0);
    $this->container->set('string_translation', $translator);
  }

  protected function mockLogger() {
    $this->mockContainer();

    // Mock the logger.factory service and a logger channel.
    $factory = $this->getMock('\Drupal\Core\Logger\LoggerChannelFactoryInterface');
    $channel = $this->getMock('\Drupal\Core\Logger\LoggerChannelInterface');
    $factory->method('get')->willReturn($channel);
    $this->container->set('logger.factory', $factory);
  }

}
