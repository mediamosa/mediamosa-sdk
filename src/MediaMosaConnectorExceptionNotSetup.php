<?php

namespace Drupal\mediamosa_sdk;

use Drupal\mediamosa_sdk\Entity\MediaMosaConnector;

/**
 * Exception thrown by connector.
 */
class MediaMosaConnectorExceptionNotSetup extends MediaMosaConnectorException {

  /**
   * Overrule to match exception type.
   *
   * @param string $message
   *   (optional) The Exception message to throw.
   * @param int $code
   *   (optional) The Exception code.
   * @param Exception $previous
   *   (optional) The previous exception used for the exception chaining.
   */
  public function __construct($message = "", $code = 0, Exception $previous = null) {
    parent::__construct($this->t('Unable to do request, connector not setup.'), MediaMosaConnector::ERROR_CONNECTOR_NOT_SETUP, $previous);
  }

}
