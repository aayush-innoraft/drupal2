<?php

namespace Drupal\content_language_access;

/**
 * Manager content language access configurations.
 *
 * @package Drupal\content_language_access
 */
class ContentLanguageAccessConfigManager {

  /**
   * Returns json encode array.
   *
   * @param string $value
   *   The name of the form element.
   *
   * @return array
   *   The json encoded array.
   */
  public function encodeArray($value = NULL) {
    $array = preg_split("/(\r\n|\n|\r)/", $value);
    return array_map('trim', $array);
  }

  /**
   * Returns string with line breaks for a form element.
   *
   * @param string $array
   *   Saved form element.
   *
   * @return string
   *   The imploded array.
   */
  public function decodeArray($array = []) {
    if (!is_array($array)) {
      return '';
    }

    return implode(PHP_EOL, $array);
  }

}
