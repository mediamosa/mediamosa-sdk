<?php
/**
 * @file
 * MediaMosa Exception classes.
 */

/**
 * The basic mediamosa_exception class
 */
class mediamosa_connector_exception extends Exception {

  /**
   * Override the constructor.
   *
   * @param string $message
   *   The Exception message to throw.
   * @param integer $code
   *   The Exception code.
   */
	public function __construct ($code, $message = '') {

    if (empty($message)) {
      switch ($code) {
        case mediamosa_connector::ERROR_CONNECTOR_NOT_SETUP:
          if (defined('DRUPAL_CORE_COMPATIBILITY') && function_exists('user_access') && user_access(mediamosa_connector::DRUPAL_PERMISSION_ADMIN)) {
            $message = t('Unable to do request, connector not setup. Setup connector !here', array('!here' => l('here' , mediamosa_connector::get_settings_form_uri(), array('external' => TRUE))));
          }
          else {
            $message = t('Unable to do request, connector not setup.');
          }
          break;

        case mediamosa_connector::ERROR_UNABLE_TO_LOGIN:
          $message = t('Unable to do request, failed to login. Check connector settings.');
          break;

        default:
          $message = t('Unknown');
          break;
      }
    }
    parent::__construct($message, $code);
  }

}
