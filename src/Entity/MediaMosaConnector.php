<?php

namespace Drupal\mediamosa_sdk\Entity;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mediamosa_sdk\MediaMosaConnectorException;
use Drupal\mediamosa_sdk\MediaMosaConnectorExceptionFailedLogin;
use Drupal\mediamosa_sdk\MediaMosaConnectorExceptionInvalidResponse;
use Drupal\mediamosa_sdk\MediaMosaConnectorExceptionNotSetup;
use Drupal\mediamosa_sdk\MediaMosaConnectorInterface;
use Drupal\mediamosa_sdk\MediaMosaException;
use Drupal\mediamosa_sdk\MediaMosaResponse;
use Drupal\mediamosa_sdk\MediaMosaSDK;
use Drupal\mediamosa_sdk\MediaMosaSDKService;
use Masterminds\HTML5\Exception;

/**
 * Defines an MediaMosa connector configuration entity.
 *
 * @ConfigEntityType(
 *   id = "mediamosa_connector",
 *   label = @Translation("MediaMosa connector"),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\mediamosa_sdk\Form\MediaMosaConnectorAddForm",
 *       "edit" = "Drupal\mediamosa_sdk\Form\MediaMosaConnectorEditForm",
 *       "delete" = "Drupal\mediamosa_sdk\Form\MediaMosaConnectorDeleteForm",
 *     },
 *     "list_builder" = "Drupal\mediamosa_sdk\MediaMosaConnectorListBuilder",
 *     "storage" = "Drupal\mediamosa_sdk\MediaMosaConnectorStorage",
 *   },
 *   admin_permission = "administer mediamosa connectors",
 *   config_prefix = "connector",
 *   entity_keys = {
 *     "id" = "name",
 *     "label" = "label",
 *     "shared_key" = "shared_key",
 *     "url" = "url",
 *     "verbose" = "verbose",
 *     "cookie" = "cookie",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/media/mediamosa-connector/manage/{mediamosa_connector}",
 *     "delete-form" = "/admin/config/media/mediamosa-connector/manage/{mediamosa_connector}/delete",
 *     "verify" = "/admin/config/media/mediamosa-connector/manage/{mediamosa_connector}/verify",
 *     "collection" = "/admin/config/media/mediamosa-connector/manage",
 *   },
 *   config_export = {
 *     "name",
 *     "label",
 *     "shared_key",
 *     "url",
 *     "verbose",
 *   }
 * )
 */
class MediaMosaConnector extends ConfigEntityBase implements MediaMosaConnectorInterface {

  use StringTranslationTrait;

  // Connector error codes.
  const ERROR_NONE = 0;
  const ERROR_UNKNOWN = 1;
  const ERROR_CONNECTOR_NOT_SETUP = 2;
  const ERROR_FAILED_LOGIN = 3;
  const ERROR_INVALID_RESPONSE = 4;

  /**
   * The machine name of the connection.
   *
   * @var string
   */
  protected $name;

  /**
   * The connection label.
   *
   * @var string
   */
  protected $label;

  /**
   * The shared key.
   *
   * @var string
   */
  protected $shared_key;

  /**
   * Verbose option for debug.
   *
   * @var int
   */
  protected $verbose;

  /**
   * The session cookie.
   *
   * @var string
   */
  protected $cookie;

  /**
   * Last error on last made call.
   *
   * @var int
   */
  private $last_error = self::ERROR_NONE;

  /**
   * Last error string on last made call.
   *
   * @var string
   */
  private $last_error_text = '';

  /**
   * Return the last error.
   *
   * @return int
   *   The error code returned by connector. Value >= 200 are MediaMosaSDK
   *   codes.
   */
  public function getLastError() {
    return $this->last_error;
  }

  /**
   * Return the last error as text.
   *
   * Will return FALSE when no error was found.
   *
   * @return string|FALSE
   *   The last error string on last call.
   */
  public function getLastErrorText() {

    if (!empty($this->last_error_text)) {
      return $this->last_error_text;
    }

    return FALSE;
  }

  /**
   * Return the name of the client application.
   *
   * @return string
   *   The name of the client application.
   */
  public function getNameApplication() {
    return (string) $this->get('label');
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getURL() {
    return $this->get('url');
  }

  /**
   * {@inheritdoc}
   */
  public function getSharedKey() {
    return $this->get('shared_key');
  }

  /**
   * {@inheritdoc}
   */
  public function getVerbose() {
    return $this->get('verbose');
  }

  /**
   * {@inheritdoc}
   */
  public function getCookie() {
    return $this->get('cookie');
  }

  /**
   * {@inheritdoc}
   */
  public function verify() {
    $this->resetSession();

    // Doing request will test the settings and login.
    try {
      $this->request('status');
    }
    catch (MediaMosaConnectorException $e) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Do the REST call request.
   *
   * @param string $path
   *   The REST uri path, e.g. 'asset'.
   * @param string $method
   *   (default 'GET') A string containing the request method.
   * @param array $data
   *   The body parameter to POST.
   * @param array $options
   *   (optional) An array which can have one or more of following keys:
   *   - fatal:
   *     Boolean value, TRUE makes the call throw an exception if the call
   *     failed. That means, failed to call, failed to access the rest etc.
   *     Empty results are not fatal. It will either return a response or
   *     throw exception. Default is FALSE (call will returns response or
   *     NULL). Use
   *   - 'login' (default TRUE): Attempt login when call fails and try again.
   *   - headers:
   *     A array containing request headers to send as name/value pairs.
   *
   * @return mediamosa_connector_response|NULL
   *   The mediamosa connect response object or NULL when fatal = FALSE. Will
   *   throw exception with any error with fatal = TRUE.
   *
   * @throws Exception()
   */
  public function request($path, $method = 'GET', array $data = array(), array $options = array()) {
    $uri = rtrim($this->getURL(), '/') . '/' . ltrim($path, '/');

    $options += array(
      // Throw Exceptions on failures, else return NULL.
      'fatal' => TRUE,
      // Try authentication on missing session.
      'login' => TRUE,
      // MediaMosa version to use.
      'mediamosa_version' => NULL,
    );

    $fatal = $options['fatal'];
    $login = $options['login'];
    if (isset($options['mediamosa_version']) && !MediaMosaSDK::isEmpty($options['mediamosa_version'])) {
      $data['mediamosa_version'] = $options['mediamosa_version'];
    }
    unset($options['login'], $options['fatal'], $options['mediamosa_version']);

    $this->last_error = self::ERROR_UNKNOWN;
    $this->last_error_text = $this->t('Unable to execute Mediamosa REST call %uri.', array('%uri' => $uri));

    try {
      if (MediaMosaSDK::isEmpty($this->url)) {
        throw new MediaMosaConnectorExceptionNotSetup;
      }

      // If cookie not set, then login first.
      if (MediaMosaSDK::isEmpty($this->cookie) && $login) {
        $this->login();
      }

      // Add our cookie.
      if (!MediaMosaSDK::isEmpty($this->cookie)) {
        $options['headers']['Cookie'] = $this->cookie;
      }

      $response = $this->doRequest($uri, $method, $data, $options);
      if ($response->isAccessDenied()) {
        $this->login();
        $response = $this->doRequest($uri, $method, $data, $options);
      }

      return $response;
    }
    catch (MediaMosaConnectorException $e) {
      $this->last_error = $e->getCode();
      $this->last_error_text = $e->getMessage();

      if ($fatal) {
        throw $e;
      }
    }
    catch (Exception $e) {
      $this->last_error = self::ERROR_UNKNOWN;
      $this->last_error_text = $e->getMessage();
      if ($fatal) {
        throw new MediaMosaException($e->getMessage(), $e->getCode(), $e);
      }
    }

    return NULL;
  }

  /**
   * Do request and log when required.
   *
   * @param string $uri
   *   The REST url.
   * @param string $method
   *   (default GET) A string containing the request method.
   * @param array $data
   *   The body parameter to POST.
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
   *
   * @see MediaMosaConnector::request()
   */
  protected function doRequest($uri, $method = 'GET', array $data = array(), array $options = array()) {
    $service = MediaMosaSDKService::getService();
    $start = microtime(TRUE);
    $response = $service->request($uri, $method, $data, $options);
    $end = microtime(TRUE);

    if ($this->getVerbose()) {
      $response_time = round($end - $start, 3);
      $service->addVerboseLog($this, $uri, $method, $response_time, $response);
    }

    return $response;
  }

  /**
   * Do request using GET method.
   *
   * @param string $path
   *  The REST path.
   * @param array $data
   *   (optional) Additional data for REST call.
   *     A array as name => value.
   * @param array $options
   *   (optional) An array which can have one or more of following keys:
   *   - fatal (default FALSE)
   *       Boolean value, TRUE will throw a exception if the call fails. Only
   *       when response was valid, MediaMosaResponse object is returned. On
   *       FALSE setting, the call will return either MediaMosaResponse object
   *       or FALSE.
   *   - headers (array)
   *       An array containing request headers to send as name/value pairs.
   *   - data
   *       A array as name => value.
   *   - max_redirects
   *       An integer representing how many times a redirect may be followed.
   *       Defaults to 1.
   *   - timeout
   *       A float representing the maximum number of seconds the function call
   *       may take. The default is 60 seconds. If a timeout occurs, the error
   *       code is set to the HTTP_REQUEST_TIMEOUT constant.
   *
   * @return MediaMosaResponse
   *   The MediaMosa response object or FALSE when fatal = FALSE. Will
   *   throw exception with any error with fatal = TRUE.
   *
   * @see MediaMosaConnector::request().
   */
  public function requestGet($path, array $data = array(), array $options = array()) {
    return $this->request($path, 'GET', $data, $options);
  }

  /**
   * Do request using POST method.
   *
   * @param string $path
   *  The REST path.
   * @param array $data
   *   (optional) Additional data for REST call.
   *     A array as name => value.
   * @param array $options
   *   (optional) An array which can have one or more of following keys:
   *   - fatal (default FALSE)
   *       Boolean value, TRUE will throw a exception if the call fails. Only
   *       when response was valid, MediaMosaResponse object is returned. On
   *       FALSE setting, the call will return either MediaMosaResponse object
   *       or FALSE.
   *   - headers (array)
   *       An array containing request headers to send as name/value pairs.
   *   - data
   *       A array as name => value.
   *   - max_redirects
   *       An integer representing how many times a redirect may be followed.
   *       Defaults to 1.
   *   - timeout
   *       A float representing the maximum number of seconds the function call
   *       may take. The default is 60 seconds. If a timeout occurs, the error
   *       code is set to the HTTP_REQUEST_TIMEOUT constant.
   *
   * @return MediaMosaResponse
   *   The MediaMosa response object or FALSE when fatal = FALSE. Will
   *   throw exception with any error with fatal = TRUE.
   *
   * @see mediamosa_connect::request().
   */
  public function requestPost($path, array $data = array(), array $options = array()) {
    return $this->request($path, 'POST', $data, $options);
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
   */
  public function log($message, array $context = array(), $level = LogLevel::INFO) {
    MediaMosaSDKService::getService()->log($message, $context, $level, 'mediamosa_connector');
  }

  /**
   * Reset the session, login will be retried.
   */
  public function resetSession() {
    $this->logout(FALSE);

    // Reset last error.
    $this->last_error = self::ERROR_NONE;
    $this->last_error_text = '';
  }

  /**
   * Logout by clearing our cookie.
   */
  protected function logout($save = TRUE) {
    $this->cookie = NULL;
    if ($save) {
      $this->save();
    }
  }

  /**
   * Check if response is valid and useful.
   *
   * @param MediaMosaResponse $response
   *   Response to check.
   *
   * @return bool
   *   Returns TRUE when valid, FALSE otherwise.
   */
  public function validResponse($response) {
    return $response instanceof MediaMosaResponse;
  }

  /**
   * Try to authenticate us on MediaMosa.
   *
   * Will throw on any failure.
   *
   * @throws \Exception
   * @throws MediaMosaConnectorException
   */
  protected function login() {
    MediaMosaConnectorException::assert(!MediaMosaSDK::isEmpty($this->getURL()), $this->t('Unable to do request, connector not setup.'));

    $this->resetSession();

    // Step 1: The challenge.
    $data = array('dbus' => 'AUTH DBUS_COOKIE_SHA1 ' . $this->getNameApplication());
    $response = $this->requestPost('login', $data, array('login' => FALSE));

    MediaMosaConnectorExceptionInvalidResponse::assert($response->isResponse(), $this->t('login call failed'));
    MediaMosaConnectorExceptionFailedLogin::assert($response->getHeaderRequestResultID() != MediaMosaSDK::HTTP_NOT_FOUND, $this->t('Enable the Application Authentication module, unable to login.'));

    // Check if we got DATA response.
    if (Unicode::substr($response->items->item->dbus, 0, 5) !== 'DATA ') {
      throw new MediaMosaConnectorExceptionFailedLogin();
    }

    $cookies = explode(';', $response->getHttpHeaderFirst('Set-Cookie'));
    foreach ($cookies as $cookie) {
      $cookie = trim($cookie);
      if (Unicode::substr($cookie, 0, 4) === 'SESS' || Unicode::substr($cookie, 0, 5) === 'SSESS') {
        $this->cookie = $cookie;
        break;
      }
    }

    if (MediaMosaSDK::isEmpty($this->cookie)) {
      throw new MediaMosaConnectorExceptionInvalidResponse($this->t('Missing session cookie from MediaMosa'));
    }

    // Step 2: Do challenge.
    $dbus = explode(' ', $response->items->item->dbus);
    $challenge = $dbus[3];
    $random = Unicode::substr(md5(microtime(TRUE)), 0, 10);
    $challenge_response = sha1(sprintf('%s:%s:%s', $challenge, $random, $this->getSharedKey()));
    $data = array('dbus' => sprintf('DATA %s %s', $random, $challenge_response));
    $response = $this->requestPost('login', $data, array('login' => FALSE));

    MediaMosaConnectorExceptionInvalidResponse::assert($response->isResponse(), 'login step 2 challenge failed');
    if (Unicode::substr($response->items->item->dbus, 0, 2) !== 'OK') {
      throw new MediaMosaConnectorExceptionFailedLogin('Login failed, check client application credentials');
    }

    // Save the session.
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name');
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

}
