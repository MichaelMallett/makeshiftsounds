<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Converter\Routing\LinkIndex.
 */

namespace Drupal\drupalmoduleupgrader\Routing;

use Doctrine\Common\Collections\ArrayCollection;
use Drupal\drupalmoduleupgrader\Routing\LinkBinding\LinkBinding;

/**
 * Represents a set of link bindings of a single type (i.e., menu links, local tasks, etc.)
 */
class LinkIndex extends ArrayCollection {

  /**
   * Tracks link IDs to prevent collisions.
   *
   * @var string[]
   */
  protected $idiotBox = [];

  /**
   * Adds a binding to this index.
   *
   * @param \Drupal\drupalmoduleupgrader\Routing\LinkBinding\LinkBinding $binding
   */
  public function addBinding(LinkBinding $binding) {
    $id = $binding->getIdentifier();

    if (isset($this->idiotBox[$id])) {
      $id .= '_' . $this->idiotBox[$id]++;
    }
    else {
      $this->idiotBox[$id] = 0;
    }

    $this->set($binding->getSource()->getPath()->__toString(), $binding);
    $binding->onIndexed($id, $this);
  }

  /**
   * Builds all the links in this index and returns them as an array of arrays,
   * keyed by link ID.
   *
   * @return array
   */
  public function build() {
    $build = [];

    foreach ($this as $binding) {
      $build[ $binding->getIdentifier() ] = $binding->build();
    }

    return $build;
  }

}
