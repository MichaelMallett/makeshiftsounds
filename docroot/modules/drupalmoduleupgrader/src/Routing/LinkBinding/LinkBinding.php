<?php

namespace Drupal\drupalmoduleupgrader\Routing\LinkBinding;

use Drupal\drupalmoduleupgrader\Routing\Drupal7\RouteWrapper as Drupal7Route;
use Drupal\drupalmoduleupgrader\Routing\Drupal8\RouteWrapper as Drupal8Route;
use Drupal\drupalmoduleupgrader\Routing\LinkIndex;

/**
 * Represents a binding between a Drupal 7 route and a Drupal 8 one.
 */
class LinkBinding {

  /**
   * @var \Drupal\drupalmoduleupgrader\Routing\Drupal7\RouteWrapper
   */
  protected $source;

  /**
   * @var \Drupal\drupalmoduleupgrader\Routing\Drupal8\RouteWrapper
   */
  protected $destination;

  /**
   * The link ID.
   *
   * @var string
   */
  protected $id;

  /**
   * Index of all other links of this type.
   *
   * @var LinkIndex
   */
  protected $index;

  /**
   * Constructs a LinkBinding object.
   */
  public function __construct(Drupal7Route $source, Drupal8Route $destination) {
    $this->source = $source;
    $this->destination = $destination;
  }

  /**
   * Returns the Drupal 7 route in this binding.
   *
   * @return \Drupal\drupalmoduleupgrader\Routing\Drupal7\RouteWrapper
   */
  public function getSource() {
    return $this->source;
  }

  /**
   * Returns the Drupal 8 route in this binding.
   *
   * @return Drupal7Route
   */
  public function getDestination() {
    return $this->destination;
  }

  /**
   * Returns the link's plugin ID.
   *
   * @return string
   */
  public function getIdentifier() {
    return $this->id ?: $this->getDestination()->getIdentifier();
  }

  /**
   * React when the binding is added to an index.
   *
   * @param string $id
   *  The link's plugin ID, sanitized to prevent collisions.
   * @param LinkIndex $index
   *  The link index.
   */
  public function onIndexed($id, LinkIndex $index) {
    $this->id = $id;
    $this->index = $index;
  }

  /**
   * Builds the link definition.
   *
   * @return array
   */
  public function build() {
    $link = [
      'route_name' => $this->getDestination()->getIdentifier(),
    ];

    $source = $this->getSource();
    if ($source->containsKey('title')) {
      $link['title'] = $source['title'];
    }
    if ($source->containsKey('weight')) {
      $link['weight'] = $source['weight'];
    }

    return $link;
  }

}
