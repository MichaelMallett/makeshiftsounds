<?php

namespace Drupal\drupalmoduleupgrader;

use Pharborist\Node;

/**
 * Represents a Drupal 7 module being run through the DMU.
 */
interface TargetInterface {

  /**
   * Returns the machine name of the target module.
   *
   * @return string
   */
  public function id();

  /**
   * Returns the base path of the target module.
   *
   * @return string
   */
  public function getBasePath();

  /**
   * Returns the path to a particular file, relative to the CWD.
   *
   * @param string $file
   *  The file, relative to the module root. If $file begins with a period,
   *  it will be prefixed with the module name (.module --> MODULE.module)
   *
   * @return string
   */
  public function getPath($file);

  /**
   * Returns a fully configured Finder which can iterate over the target
   * module's code files. Any file type which doesn't contain PHP code
   * should be ignored.
   *
   * @return \Symfony\Component\Finder\Finder
   */
  public function getFinder();

  /**
   * Returns an indexer for this target.
   *
   * @param string $which
   *  The type of indexer to get. Should be the ID of an indexer plugin.
   *
   * @return IndexerInterface
   */
  public function getIndexer($which);

  /**
   * Returns services defined by the target module.
   *
   * @return \Doctrine\Common\Collections\ArrayCollection
   */
  public function getServices();

  /**
   * Returns if the target module implements a particular hook.
   *
   * @param string $hook
   *  The hook to look for, without the hook_ prefix.
   *
   * @return boolean
   */
  public function implementsHook($hook);

  /**
   * Executes a hook implementation and returns the result.
   *
   * @param string $hook
   *  The hook to execute, without the hook_ prefix.
   * @param array $arguments
   *  Additional parameters to pass to the hook implementation.
   *
   * @return mixed
   *
   * @throws
   *  \InvalidArgumentException if the module doesn't implement the hook.
   *  \LogicException if the hook contains non-executable logic.
   */
  public function executeHook($hook, array $arguments = []);

  /**
   * Parses a file into a syntax tree, keeping a reference to it, and
   * returns it.
   *
   * @param string $file
   *  The path of the file to open, relative to the CWD.
   *
   * @return \Pharborist\RootNode|NULL
   */
  public function open($file);

  /**
   * Saves the file in which a particular node appears.
   *
   * @param \Pharborist\Node|NULL $node
   *  The node to save. This can be positioned anywhere in the
   *  syntax tree. If NULL, all open files will be saved.
   *
   * @throws \Drupal\drupalmoduleupgrader\IOException
   */
  public function save(Node $node = NULL);

  /**
   * Creates a new, empty document.
   *
   * @param string $file
   *  The path of the file to create, relative to the CWD.
   *
   * @return \Pharborist\RootNode
   */
  public function create($file);

  /**
   * Clears internal references to all open documents, discarding changes.
   */
  public function flush();

}
