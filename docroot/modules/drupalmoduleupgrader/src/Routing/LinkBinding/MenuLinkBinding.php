<?php

namespace Drupal\drupalmoduleupgrader\Routing\LinkBinding;

/**
 * Represents a standard menu link.
 */
class MenuLinkBinding extends LinkBinding {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $link = parent::build();

    $source = $this->getSource();
    if ($source->containsKey('description')) {
      $link['description'] = $source['description'];
    }

    $destination = $this->getDestination();
    if ($destination->hasParent()) {
      $link['parent'] = $destination->getParent()->getIdentifier();
    }

    return $link;
  }

}
