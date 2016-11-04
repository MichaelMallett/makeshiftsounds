<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer;

use Drupal\drupalmoduleupgrader\AnalyzerBase;
use Drupal\drupalmoduleupgrader\TargetInterface;

/**
 * @Analyzer(
 *  id = "_flag_hook",
 *  deriver = "\Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer\FlagHookDeriver"
 * )
 */
class FlagHook extends AnalyzerBase {

  /**
   * {@inheritdoc}
   */
  public function analyze(TargetInterface $target) {
    $hook = 'hook_' . $this->pluginDefinition['hook'];
    $indexer = $target->getIndexer('function');

    if ($indexer->has($hook)) {
      return [$this->buildIssue($target)->addViolation($indexer->get($hook), $this)];
    }
    else {
      return [];
    }
  }

}
