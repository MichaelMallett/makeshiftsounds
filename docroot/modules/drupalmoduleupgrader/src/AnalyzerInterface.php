<?php

namespace Drupal\drupalmoduleupgrader;

/**
 * Interface for plugins which can analyze a target module and flag potential
 * or existing issues.
 */
interface AnalyzerInterface {

  /**
   * Analyzes a target module and flags any issues found.
   *
   * @param TargetInterface $target
   *  The target module.
   *
   * @return \Drupal\drupalmoduleupgrader\IssueInterface[]
   */
  public function analyze(TargetInterface $target);

}
