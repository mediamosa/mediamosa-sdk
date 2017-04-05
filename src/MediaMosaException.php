<?php

namespace Drupal\mediamosa_sdk;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Error;

/**
 * The default MediaMosa exception.
 */
class MediaMosaException extends \RuntimeException {

  use StringTranslationTrait;

  /**
   * Throw exception when $test fails.
   *
   * @param mixed $test
   *   Any value that needs to be tested.
   * @param string $message
   *   (optional) The message to show.
   *
   * @throws RtmException
   *   When test fails.
   */
  public static function assert($test, $message = '') {
    if (!$test) {
      $backtrace = debug_backtrace();
      $caller = Error::getLastCaller($backtrace);
      $class = function_exists('get_called_class') ? get_called_class() : __CLASS__;
      throw new $class(
        strtr('{message}Assertion failed in {function} {file} on line {line}.',
        array(
          '{function}' => $caller['function'],
          '{line}' => $caller['line'],
          '{file}' => $caller['file'],
          '{message}' => empty($message) ? '' : ($message . ', '),
        ))
      );
    }
  }

}
