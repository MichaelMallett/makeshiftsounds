<?php

namespace Drupal\drupalmoduleupgrader\Routing\LinkBinding;

/**
 * Represents a local action.
 */
class LocalActionLinkBinding extends LinkBinding {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $link = parent::build();
    $link['appears_on'][] = $this->getDestination()->getIdentifier();

    return $link;
  }

}
