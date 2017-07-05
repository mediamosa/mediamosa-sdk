<?php

namespace Drupal\mediamosa_sdk;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mediamosa_sdk\Entity\MediaMosaConnector;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LogLevel;

/**
 * MediaMosa SDK service.
 */
class MediaMosaSDKService {

  use StringTranslationTrait;

  /**
   * The maximum entries in verbose log before truncating it.
   *
   * @var int
   */
  public $verbose_log_max_size = 100;

  /**
   * Returns the global service object.
   *
   * @return MediaMosaSDKService
   *   The MediaMosa SDK service.
   */
  public static function getService() {
    return \Drupal::service('mediamosa_sdk_service');
  }

  /**
   * Returns the connector entity.
   *
   * @param mixed $id
   *   The ID of the entity to load.
   *
   * @return \Drupal\mediamosa_sdk\Entity\MediaMosaConnector|null
   *   An entity object. NULL if no matching entity is found.
   */
  public function getConnector($id) {
    return MediaMosaConnector::load($id);
  }

  /**
   * Log an message.
   *
   * @param string $message
   *   The message to log.
   * @param array $context
   *   Key => value for content in message.
   * @param string $level
   *   See Psr\Log\LogLevel\LogLevel::*.
   * @param string $channel
   *   (default 'mediamosa_sdk') Our log channel.
   */
  public function log($message, array $context = array(), $level = LogLevel::INFO, $channel = 'mediamosa_sdk') {
    \Drupal::logger($channel)->log($level, $message, $context);
  }

  /**
   * Do the REST call request.
   *
   * Function is mapped the same as previous versions of the
   * mediamosa_connector::request().
   *
   * @param string $uri
   *   The REST uri.
   * @param string $method
   *   (default GET) A string containing the request method.
   * @param array $data
   *   The GET/POST parameters.
   * @param array $options
   *   (optional) An array which can have one or more of following keys:
   *   - headers:
   *     A array containing request headers to send as name/value pairs.
   *
   * @return MediaMosaResponse
   *   The mediamosa connect response object or FALSE when fatal = FALSE. Will
   *   throw exception with any error with fatal = TRUE.
   *
   * @throws MediaMosaException
   */
  public function request($uri, $method = 'GET', array $data = array(), array $options = array()) {
    $headers = isset($options['headers']) ? $options['headers'] : array();
    $headers += array(
      'Accept' => 'text/xml',
    );

    try {
      $options = array(
        'headers' => $headers,
      );

      if (strtoupper($method) === 'POST') {
        $options['form_params'] = $data;
      }
      else {
        $options['query'] = $data;
      }

      $response = \Drupal::httpClient()->request($method, (string) $uri, $options);
      /* @var $response ResponseInterface */
    }
    catch (\Exception $e) {
      throw new MediaMosaException($this->t('Failed call %uri to MediaMosa; %message.', array('%uri' => $uri, '%message' => $e->getMessage())));
    }

    MediaMosaException::assert($response instanceof ResponseInterface, $this->t('Failed call %uri to MediaMosa.', array('%uri' => $uri)));
    return new MediaMosaResponse($response->getHeaders(), $response->getBody());
  }

  /**
   * Returns the verbose log.
   *
   * @param bool $truncate
   *   Remove the log too.
   *
   * @return array
   *   The verbose log.
   */
  public function getVerboseLog($truncate = FALSE) {
    $verbose_log = \Drupal::service('user.private_tempstore')->get('mediamosa_sdk')->get('verbose_log');
    if ($truncate) {
      $this->truncateVerboseLog();
    }
    return !MediaMosaSDK::isEmpty($verbose_log) ? $verbose_log : array();
  }

  /**
   * Returns the verbose log.
   *
   * @param array $verbose_log
   *   The verbose log.
   */
  public function setVerboseLog(array $verbose_log = array()) {
     \Drupal::service('user.private_tempstore')->get('mediamosa_sdk')->set('verbose_log', $verbose_log);
  }

  /**
   * Remove the verbose log.
   */
  public function truncateVerboseLog() {
    $this->setVerboseLog();
  }

  /**
   * Log an request made.
   *
   * @param \Drupal\mediamosa_sdk\Entity\MediaMosaConnector $connector
   *   The connector which made the call.
   * @param string $uri
   *   The complete uri of the call.
   * @param $method
   *   The http method used.
   * @param float $response_time
   *   The response time.
   * @param \Drupal\mediamosa_sdk\MediaMosaResponse|NULL $mediamosa_response
   *   (optional) The response object result.
   */
  public function addVerboseLog(MediaMosaConnector $connector, $uri, $method, $response_time, MediaMosaResponse $mediamosa_response = NULL) {

    if (!$connector->getVerbose()) {
      return;
    }

    $verbose_log = $this->getVerboseLog();
    // Truncate log to keep size in check.
    if (count($verbose_log) > $this->verbose_log_max_size) {
      array_shift($verbose_log);
    }

    $verbose_log[$connector->getName()][] = array(
      'method' => $method,
      'uri' => $uri,
      'response_time' => $response_time,
      'result_id' => $mediamosa_response instanceof MediaMosaResponse ? $mediamosa_response->getHeaderRequestResultID() : 0,
      'result_description' => $mediamosa_response instanceof MediaMosaResponse ? $mediamosa_response->getHeaderRequestResultDescription() : '',
      'errors' =>  $mediamosa_response instanceof MediaMosaResponse ? $mediamosa_response->getHeaderRequestResultErrors() : array(),
      'data' => $mediamosa_response instanceof MediaMosaResponse ? $mediamosa_response->getResponseRendered() : NULL,
    );

    $this->setVerboseLog($verbose_log);
  }

}
