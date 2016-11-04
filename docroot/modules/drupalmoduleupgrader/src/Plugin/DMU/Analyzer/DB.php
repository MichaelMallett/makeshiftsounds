<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer;

use Drupal\drupalmoduleupgrader\AnalyzerBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Types\StringNode;

/**
 * @Analyzer(
 *  id = "_db",
 *  message = @Translation("Certain database tables have been removed."),
 *  tags = {
 *    "category" = { "db" }
    },
 *  deriver = "\Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer\DBDeriver"
 * )
 */
class DB extends AnalyzerBase {

  /**
   * Tables which will cause the function call to be commented out.
   *
   * @var string[]
   */
  protected static $forbiddenTables = ['variable'];

  /**
   * {@inheritdoc}
   */
  public function analyze(TargetInterface $target) {
    $function_calls = $target
      ->getIndexer('function_call')
      ->get($this->pluginDefinition['function'] ?: $this->getPluginId())
      ->filter(function(FunctionCallNode $function_call) {
        $arguments = $function_call->getArguments();
        return $arguments[0] instanceof StringNode && in_array($arguments[0]->toValue(), self::$forbiddenTables);
      });

    $issues = [];
    if ($function_calls->count() > 0) {
      $issue = $this->buildIssue($target);
      $function_calls->each(function(FunctionCallNode $function_call) use ($issue) {
        $issue->addViolation($function_call, $this);
      });
      $issues[] = $issue;
    }

    return $issues;
  }

}
