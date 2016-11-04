<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer;

use Drupal\drupalmoduleupgrader\AnalyzerBase;
use Drupal\drupalmoduleupgrader\Issue;
use Drupal\drupalmoduleupgrader\TargetInterface;

/**
 * @Analyzer(
 *  id = "info",
 *  description = @Translation("Analyzes Drupal 7 info files."),
 *  documentation = {
 *    {
 *      "url" = "https://www.drupal.org/node/1935708",
 *      "title" = @Translation("`.info` files are now `.info.yml` files")
 *    }
 *  }
 * )
 */
class InfoFile extends AnalyzerBase {

  /**
   * {@inheritdoc}
   */
  public function analyze(TargetInterface $target) {
    $issues = [];
    $info_file = $target->getPath('.info');
    if (! file_exists($info_file)) {
      return $issues;
    }

    $info = \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\InfoToYAML::parseInfo($info_file);
    if (empty($info)) {
      throw new \RuntimeException('Cannot parse info file ' . $info_file);
    }

    $doc = $this->pluginDefinition['documentation'][0];
    if ($info['core'] != '8.x') {
      $issues['core'] = new Issue($target, $this->t("Module info files' `core` key must have a value of `8.x`."));
      $issues['core']->addDocumentation($doc['url'], $doc['title']);
    }
    if (empty($info['type'])) {
      $issues['type'] = new Issue($target, $this->t('Info files must contain a `type` key.'));
      $issues['type']->addDocumentation($doc['url'] . '#type', $doc['title']);
    }
    if (isset($info['dependencies'])) {
      $issues['dependencies'] = new Issue($target, $this->t('Many common dependencies have moved into core.'));
      $issues['dependencies']->addDocumentation($doc['url'], $doc['title']);
    }
    if (isset($info['files'])) {
      $issues['files'] = new Issue($target, $this->t('Modules no longer declare classes in their info file.'));
      $issues['files']->addDocumentation($doc['url'] . '#files', $doc['title']);
    }
    if (isset($info['configure'])) {
      $issues['configure'] = new Issue($target, $this->t("Module info files' `configure` key must be a route name, not a path."));
      $issues['configure']->addDocumentation($doc['url'] . '#configure', $doc['title']);
    }

    /** @var \Drupal\drupalmoduleupgrader\IssueInterface $issue */
    foreach ($issues as $key => $issue) {
      $issue->setTag('error_level', 'error');
      $issue->setTag('category', ['info']);
      $issue->addAffectedFile($info_file, $this);
    }

    return $issues;
  }

}
