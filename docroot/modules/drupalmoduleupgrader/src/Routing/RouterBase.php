<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Converter\Routing\RouterBase.
 */

namespace Drupal\drupalmoduleupgrader\Routing;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Base class for RouterInterface implementations.
 */
class RouterBase extends ArrayCollection implements RouterInterface {

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcher
   */
  protected $dispatcher;

  /**
   * Constructs a RouterBase.
   */
  public function __construct(array $elements = []) {
    parent::__construct($elements);
    $this->dispatcher = new EventDispatcher();
  }

  /**
   * {@inheritdoc}
   */
  public function addRoute(RouteWrapperInterface $route) {
    $this->set($route->getIdentifier(), $route);
    $this->dispatcher->addListener('router.built', [ $route, 'onRouterBuilt' ]);
  }

  /**
   * {@inheritdoc}
   */
  public function finalize() {
    $this->dispatcher->dispatch('router.built', new RouterBuiltEvent($this));
  }

}
