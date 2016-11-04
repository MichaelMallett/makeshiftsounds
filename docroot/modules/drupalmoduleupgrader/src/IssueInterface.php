<?php

namespace Drupal\drupalmoduleupgrader;

use Pharborist\Node;

interface IssueInterface {

  /**
   * Returns the title of the issue.
   *
   * @return string
   */
  public function getTitle();

  /**
   * Sets the title of the issue.
   *
   * @param string $title
   *
   * @return $this
   */
  public function setTitle($title);

  /**
   * Returns the issue summary.
   *
   * @return string
   */
  public function getSummary();

  /**
   * Sets the issue summary.
   *
   * @param string $summary
   *
   * @return $this
   */
  public function setSummary($summary);

  /**
   * Adds a piece of documentation relevant to the issue.
   *
   * @param string $url
   *  The documentation's full URL.
   * @param string $title
   *  The documentation's displayed title.
   *
   * @return $this
   */
  public function addDocumentation($url, $title);

  /**
   * Returns all documentation as an array of arrays, each containing 'url'
   * and 'title' keys.
   *
   * @return array
   */
  public function getDocumentation();

  /**
   * Marks a particular file as being affected by this issue.
   *
   * @param string $file
   *  The path of the affected file.
   * @param \Drupal\drupalmoduleupgrader\AnalyzerInterface $detector
   *  The plugin which detected the problem.
   *
   * @return $this
   */
  public function addAffectedFile($file, AnalyzerInterface $detector);

  /**
   * Flags a single violation of this issue in a particular syntax node.
   *
   * @param \Pharborist\Node $node
   *  The offending syntax tree node.
   * @param \Drupal\drupalmoduleupgrader\AnalyzerInterface $detector
   *  The plugin which detected the violation.
   *
   * @return $this
   */
  public function addViolation(Node $node, AnalyzerInterface $detector);

  /**
   * Returns all violations as an array of arrays, each of which has a 'file' key
   * (required), and an optional 'line_number' key.
   *
   * @return array
   */
  public function getViolations();

  /**
   * Returns the fully qualified names of every plugin which detected violations,
   * as set by addAffectedFile() and addViolation().
   *
   * @return string[]
   */
  public function getDetectors();

  /**
   * Returns if a tag is set on the issue.
   *
   * @param string $tag
   *  The tag's name.
   *
   * @return boolean
   */
  public function hasTag($tag);

  /**
   * Returns the value set for a tag. The tag value can be anything; the
   * meaning of the value depends on the tag.
   *
   * @param string $tag
   *  The tag's name.
   *
   * @return mixed
   */
  public function getTag($tag);

  /**
   * Sets the value for a tag. Any existing value for the tag will be
   * blown away.
   *
   * @param string $tag
   *  The tag's name.
   * @param mixed $value
   *  The tag value. Can be anything.
   *
   * @return $this
   */
  public function setTag($tag, $value);

  /**
   * Clears all values for a tag.
   *
   * @param string $tag
   *  The tag's name.
   *
   * @return $this
   */
  public function clearTag($tag);

  /**
   * Gets all fixes queued for this issue. Each fix will be an array with at
   * least a _plugin_id element, containing the plugin ID of the fixer to use.
   * Everything else will be given to the fixer as configuration.
   *
   * @return array[]
   */
  public function getFixes();

  /**
   * Adds a fix for this issue.
   *
   * @param string $fixer_id
   *  The plugin ID of the fixer to use.
   * @param array $configuration
   *  Optional configuration for the fixer.
   *
   * @return $this
   */
  public function addFix($fixer_id, array $configuration = []);

}
