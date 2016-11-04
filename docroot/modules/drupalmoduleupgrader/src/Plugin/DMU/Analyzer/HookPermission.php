<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer;

use Drupal\drupalmoduleupgrader\AnalyzerBase;
use Drupal\drupalmoduleupgrader\TargetInterface;

/**
 * @Analyzer(
 *  id = "hook_permission",
 *  description = @Translation("Analyzes implementations of hook_permission()."),
 *  documentation = {
 *    {
 *      "url" = "https://www.drupal.org/node/2311427",
 *      "title" = @Translation("Defining permissions in `MODULE.permissions.yml`")
 *    }
 *  },
 *  tags = {
 *    "category" = { "system", "user" },
 *    "error_level" = "warning"
 *  },
 *  hook = "hook_permission",
 *  message = @Translation("Static permissions are now defined in `MODULE.permissions.yml`.")
 * )
 */
class HookPermission extends AnalyzerBase {

  /**
   * {@inheritdoc}
   */
  public function analyze(TargetInterface $target) {
    $issues = [];
    $indexer = $target->getIndexer('function');

    if ($indexer->hasExecutable('hook_permission')) {
      $issues[] = $this
        ->buildIssue($target)
        ->addViolation($indexer->get('hook_permission'), $this)
        ->addFix('hook_to_YAML', [
          'hook' => 'permission',
          'destination' => '~/' . $target->id() . '.permissions.yml',
        ]);
    }

    return $issues;
  }

}
