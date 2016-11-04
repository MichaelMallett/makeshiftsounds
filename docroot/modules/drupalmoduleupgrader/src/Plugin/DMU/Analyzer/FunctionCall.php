<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer;

use Drupal\drupalmoduleupgrader\AnalyzerBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;

/**
 * @Analyzer(
 *  id = "_function_call",
 *  deriver = "Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer\FunctionCallDeriver"
 * )
 */
class FunctionCall extends AnalyzerBase {

  /**
   * {@inheritdoc}
   */
  public function analyze(TargetInterface $target) {
    $indexer = $target->getIndexer('function_call');
    $issues = [];

    if ($indexer->has($this->pluginDefinition['function'])) {
      $issue = $this->buildIssue($target);

      $indexer
        ->get($this->pluginDefinition['function'])
        ->each(function(FunctionCallNode $function_call) use ($issue) {
          $issue->addViolation($function_call, $this);
        });

      $issues[] = $issue;
    }

    return $issues;
  }

}
