<?php

namespace Drupal\drupalmoduleupgrader;

use cebe\markdown\Markdown;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Pharborist\Node;

class Issue implements IssueInterface {

  /**
   * @var TargetInterface
   */
  protected $target;

  /**
   * @var string
   */
  protected $title;

  /**
   * @var string
   */
  protected $summary;

  /**
   * @var array
   */
  protected $documentation = [];

  /**
   * @var array
   */
  protected $violations = [];

  /**
   * @var AnalyzerInterface[]
   */
  protected $detectors = [];

  /**
   * @var mixed[]
   */
  protected $tags = [];

  /**
   * @var array[]
   */
  protected $fixes = [];

  /**
   * @var \cebe\markdown\Markdown
   */
  protected $parser;

  public function __construct(Target $target, $title, $summary = NULL) {
    $this->target = $target;
    $this->setTitle($title);

    if (isset($summary)) {
      $this->setSummary($summary);
    }

    $this->parser = new Markdown();
    $this->parser->html5 = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->parser->parseParagraph($this->title);
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->title = (string) $title;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return $this->parser->parse($this->summary);
  }

  /**
   * {@inheritdoc}
   */
  public function setSummary($summary) {
    $this->summary = (string) $summary;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addDocumentation($url, $title) {
    $this->documentation[] = [
      'url' => $url,
      'title' => $this->parser->parseParagraph($title),
    ];
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDocumentation() {
    return $this->documentation;
  }

  /**
   * {@inheritdoc}
   */
  public function addAffectedFile($file, AnalyzerInterface $detector) {
    if (empty($this->violations[$file])) {
      $this->violations[$file] = [];
    }
    $this->addDetector($detector);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addViolation(Node $node, AnalyzerInterface $detector) {
    $file = $node->getFilename();
    if ($file) {
      $this->violations[$file][] = [
        'line_number' => $node->getLineNumber(),
      ];
    }
    else {
      throw new \DomainException('Cannot record an issue violation from a detached node.');
    }
    $this->addDetector($detector);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getViolations() {
    $return_violations = [];

    foreach ($this->violations as $file => $file_violations) {
      if ($file_violations) {
        foreach ($file_violations as $violation) {
          $violation['file'] = $file;
          $return_violations[] = $violation;
        }
      }
      else {
        $return_violations[] = ['file' => $file];
      }
    }

    return $return_violations;
  }

  /**
   * {@inheritdoc}
   */
  public function getDetectors() {
    return array_unique($this->detectors);
  }

  /**
   * {@inheritdoc}
   */
  public function hasTag($tag) {
    return array_key_exists($tag, $this->tags);
  }

  /**
   * {@inheritdoc}
   */
  public function getTag($tag) {
    return $this->tags[$tag];
  }

  /**
   * {@inheritdoc}
   */
  public function setTag($tag, $value) {
    $this->tags[$tag] = $value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function clearTag($tag) {
    unset($this->tags[$tag]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFixes() {
    return $this->fixes;
  }

  /**
   * {@inheritdoc}
   */
  public function addFix($fixer_id, array $configuration = []) {
    $this->fixes[] = array_merge($configuration, ['_plugin_id' => $fixer_id]);
    $this->setTag('fixable', TRUE);
    return $this;
  }

  /**
   * Stores a reference to an issue detector, if we don't already know about it,
   * for use by getDetectors().
   *
   * @param AnalyzerInterface $detector
   */
  protected function addDetector(AnalyzerInterface $detector) {
    if ($detector instanceof PluginInspectionInterface) {
      $this->detectors[] = $detector->getPluginId();
    }
  }

}
