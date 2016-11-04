<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer;

use Drupal\drupalmoduleupgrader\AnalyzerBase;
use Drupal\drupalmoduleupgrader\TargetInterface;

/**
 * @Analyzer(
 *  id = "grep",
 *  description = @Translation("Searches for and replaces commonly-used code that has changed in Drupal 8."),
 *  documentation = {
 *    {
 *      "url" = "https://www.drupal.org/node/2324935",
 *      "title" = @Translation("The global theme variables have been replaced by an ActiveTheme object")
 *    }
 *  },
 *  tags = {
 *    "category" = { "misc" },
 *    "error_level" = "warning"
 *  },
 *  message = @Translation("Many common functions, shared variables, and constants have been renamed.")
 * )
 */
class Grep extends AnalyzerBase {

  /**
   * {@inheritdoc}
   */
  public function analyze(TargetInterface $target) {
    // It's too odious to try and grep through the entire module for the various
    // targets. So we'll just unconditionally flag the issue.
    return [$this->buildIssue($target)];
  }

}
