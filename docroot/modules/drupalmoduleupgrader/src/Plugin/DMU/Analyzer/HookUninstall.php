<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer;

use Drupal\drupalmoduleupgrader\AnalyzerBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Filter;
use Pharborist\Functions\FunctionCallNode;

/**
 * @Analyzer(
 *  id = "hook_uninstall",
 *  description = @Translation("Removes variable_del() calls from hook_uninstall()."),
 *  message = @Translation("Default configuration is deleted automatically."),
 *  tags = {
 *    "category" = { "config" },
 *    "error_level" = "warning"
 *  },
 *  hook = "hook_uninstall"
 * )
 */
class HookUninstall extends AnalyzerBase {

  /**
   * {@inheritdoc}
   */
  public function analyze(TargetInterface $target) {
    $indexer = $target->getIndexer('function');
    $issues = [];

    if ($indexer->has('hook_uninstall')) {
      /** @var \Pharborist\NodeCollection $variable_del */
      $variable_del = $indexer->get('hook_uninstall')->find(Filter::isFunctionCall('variable_del'));

      if (sizeof($variable_del) > 0) {
        $issue = $this->buildIssue($target);
        $variable_del->each(function(FunctionCallNode $function_call) use ($issue) {
          $issue->addViolation($function_call, $this);
        });
        $issues[] = $issue;
      }
    }

    return $issues;
  }

}
