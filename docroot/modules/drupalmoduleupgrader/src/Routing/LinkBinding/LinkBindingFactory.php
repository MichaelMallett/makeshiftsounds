<?php

namespace Drupal\drupalmoduleupgrader\Routing\LinkBinding;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\drupalmoduleupgrader\Routing\Drupal7\RouteWrapper as Drupal7Route;
use Drupal\drupalmoduleupgrader\Routing\Drupal8\RouteWrapper as Drupal8Route;

/**
 * Factory class to create link bindings, depending on the source route's type.
 */
class LinkBindingFactory {

  /**
   * @var PluginManagerInterface
   */
  private $linkManager;

  public function __construct(PluginManagerInterface $link_manager) {
    $this->linkManager = $link_manager;
  }

  /**
   * Factory method. Returns a link binding object appropriate for the source link type.
   *
   * @param Drupal7Route $source
   *  The source (Drupal 7) route.
   * @param Drupal8Route $destination
   *  The destination (Drupal 8) route.
   *
   * @return mixed
   *  A link binding object; either an instance of this class or a subclass thereof.
   */
  public function create(Drupal7Route $source, Drupal8Route $destination) {
    if ($source->isLink()) {
      return new MenuLinkBinding($source, $destination);
    }
    elseif ($source->isLocalTask() || $source->isDefaultLocalTask()) {
      return new LocalTaskLinkBinding($source, $destination, $this->linkManager);
    }
    elseif ($source->isLocalAction()) {
      if ($source->isContextualLink()) {
        return new LinkBinding($source, $destination);
      }
      else {
        return new LocalActionLinkBinding($source, $destination);
      }
    }
  }

}
