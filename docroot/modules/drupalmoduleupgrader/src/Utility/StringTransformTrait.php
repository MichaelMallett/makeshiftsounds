<?php

/**
 * @file
 * Contains Drupal\drupalmoduleupgrader\Utility\StringTransformTrait.
 */

namespace Drupal\drupalmoduleupgrader\Utility;

/**
 * Contains methods for transforming strings in various helpful ways.
 */
trait StringTransformTrait {

  /**
   * Converts a string toCamelCase :)
   *
   * @param string $string
   *  The string to convert.
   *
   * @return string
   */
  public function toCamelCase($string) {
    return preg_replace_callback('/_[a-z]/', function (array $match) { return strToUpper($match[0]{1}); }, $string);
  }

  /**
   * Converts a string ToTitleCase.
   *
   * @param string $string
   *  The string to convert.
   *
   * @return string
   */
  public function toTitleCase($string) {
    $string = $this->toCamelCase($string);
    $string{0} = strToUpper($string{0});

    return $string;
  }

  /**
   * Trims a prefix (as well as leading or trailing underscore, if any) from a
   * string.
   *
   * @param string $string
   *  The string to process.
   * @param string $prefix
   *  The prefix to trim off, without leading or trailing underscores.
   *
   * @return string
   */
  public function unPrefix($string, $prefix) {
    return preg_replace('/^_?' . $prefix . '_/', NULL, $string);
  }

  /**
   * Trims a suffix (as well as leading underscore, if any) from a string.
   *
   * @param string $string
   *  The string to process.
   * @param string $suffix
   *  The suffix to trim off, without leading underscore.
   *
   * @return string
   */
  public function unSuffix($string, $suffix) {
    return preg_replace('/^_?' . $suffix . '$/', NULL, $string);
  }

  /**
   * Deletes {wildcards} from a route path.
   *
   * @param string $path
   *
   * @return string
   */
  public function deleteWildcards($path) {
    return preg_replace('/\/?\{([a-zA-Z0-9_]+)\}/', NULL, $path);
  }

  /**
   * Deletes %wildcards from a route path.
   *
   * @param string $path
   *
   * @return string
   */
  public function deleteLegacyWildcards($path) {
    return preg_replace('/\/?%[a-zA-Z0-9_]+/', NULL, $path);
  }

  /**
   * Generates an identifier from a Drupal 7 path.
   *
   * @param string $path
   *  The input path, including any %wildcards.
   *
   * @return string
   *  The identifier
   */
  public function getIdentifierFromLegacyPath($path) {
    return $this->getIdentifierFromPath($this->deleteLegacyWildcards($path));
  }

  /**
   * Generates an identifier from a path.
   *
   * @param string $path
   *  The input path, including any {wildcards}.
   *
   * @return string
   *  The identifier.
   */
  public function getIdentifierFromPath($path) {
    return $this->getIdentifier($this->deleteWildcards($path));
  }

  /**
   * Generates an identifier (prefixed with the module name, if $this->module exists)
   * from an arbitrary string.
   *
   * @param $string
   *  The input string.
   *
   * @return string
   *  The identifier.
   */
  public function getIdentifier($string) {
    // Replace all non-alphanumeric character sequences with an underscore.
    $id = preg_replace('/[^a-zA-Z0-9_]+/', '_', $string);

    if (isset($this->module)) {
      // If the name begins with MODULE_, replace that underscore with a period. Otherwise,
      // prefix the key with the module's machine name. We want all routes to look like
      // MODULE.route.
      if (strIPos($id, $this->module->getMachineName() . '_') === 0) {
        $id = preg_replace('/_/', '.', $id, 1);
      }
      else {
        $id = $this->module->getMachineName() . '.' . $id;
      }
    }

    return $id;
  }

}
