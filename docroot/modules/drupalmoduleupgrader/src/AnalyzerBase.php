<?php

namespace Drupal\drupalmoduleupgrader;

/**
 * Base class for analyzers.
 */
abstract class AnalyzerBase extends PluginBase implements AnalyzerInterface {

  /**
   * Creates an issue with title, summary, documentation and tags pulled from
   * the plugin definition.
   *
   * @param TargetInterface $target
   *  The target module.
   *
   * @return IssueInterface
   */
  protected function buildIssue(TargetInterface $target) {
    $issue = new Issue($target, $this->pluginDefinition['message'], $this->pluginDefinition['summary']);

    foreach ($this->pluginDefinition['documentation'] as $doc) {
      $issue->addDocumentation($doc['url'], $doc['title']);
    }

    foreach ($this->pluginDefinition['tags'] as $group => $tag) {
      $issue->setTag($group, $tag);
    }

    // If the plugin definition didn't supply an error_level tag, mark this
    // one as an error.
    if (empty($this->pluginDefinition['tags']['error_level'])) {
      $issue->setTag('error_level', 'error');
    }

    return $issue;
  }

}
