<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Converter\Routing\RouterBuiltEvent.
 */

namespace Drupal\drupalmoduleupgrader\Routing;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event object fired when all routes have been added to a RouterInterface
 * implementation.
 */
class RouterBuiltEvent extends Event {

  /**
   * @var \Drupal\drupalmoduleupgrader\Converter\Routing\RouterInterface
   */
  protected $router;

  /**
   * Constructs a RouterBuiltEvent object.
   */
  public function __construct(RouterInterface $router) {
    $this->router = $router;
  }

  /**
   * Returns the router object.
   *
   * @return \Drupal\drupalmoduleupgrader\Converter\Routing\RouterInterface
   */
  public function getRouter() {
    return $this->router;
  }

}
