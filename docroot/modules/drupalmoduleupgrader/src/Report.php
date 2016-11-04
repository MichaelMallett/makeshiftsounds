<?php

namespace Drupal\drupalmoduleupgrader;

/**
 * Basic implementation of an analyzer report.
 */
class Report implements ReportInterface {

  /**
   * @var \Drupal\drupalmoduleupgrader\IssueInterface[]
   */
  protected $issues = [];

  /**
   * {@inheritdoc}
   */
  public function addIssue(IssueInterface $issue) {
    $id = spl_object_hash($issue);
    $this->issues[$id] = $issue;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getIssues($tag = NULL) {
    // We call array_values() here to reset the keys.
    $issues = array_values($this->issues);

    if ($tag) {
      $issues = array_filter($issues, function(IssueInterface $issue) use ($tag) {
        return $issue->hasTag($tag);
      });
    }

    return $issues;
  }

  public function enumerateTag($tag) {
    $enum = array_map(function(IssueInterface $issue) use ($tag) { return $issue->getTag($tag); }, $this->getIssues($tag));
    return array_unique($enum);
  }

}
