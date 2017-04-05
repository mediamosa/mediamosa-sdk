<?php

namespace Drupal\mediamosa_sdk;

use Drupal\Component\Utility\Unicode;

/**
 * Helper class to support output format of MediaMosa REST.
 *
 * We do not extend on the SimpleXMLElement because we will support other
 * formats besides XML.
 */
class MediaMosaResponse {

  const VERSION_MAJOR = 'major';
  const VERSION_MINOR = 'minor';
  const VERSION_RELEASE = 'release';
  const VERSION_BUILD = 'build';
  const VERSION_INFO = 'info';

  /**
   * The SimpleXMLElement object.
   *
   * @var \SimpleXMLElement
   */
  public $_xml = NULL;

  /**
   * The HTTP header.
   *
   * @var array
   */
  public $_http_header = array();

  /**
   * Setup the data returned from the REST call and its content-type.
   *
   * @param array $http_header
   *   The HTTP header received. Must at least contain Content-Type. Keys are
   *   lower-cased stored for case insensitive retrieval.
   * @param string $data
   *  The raw data returned by the REST call.
   *
   * @throws MediaMosaException
   */
  public function __construct(array $http_header, $data) {
    MediaMosaException::assert(!MediaMosaSDK::isEmpty($data), 'Data can not be empty');

    static::setHttpHeaders($http_header);
    $content_type = $this->getHttpHeaderFirst('content-type');
    MediaMosaException::assert(!MediaMosaSDK::isEmpty($content_type), 'Need Content-Type in header');

    self::setResponse($data, $content_type);
  }

  /**
   * Magic function to support -> on object.
   *
   * @param string $name
   *   The name of the variable to get from response.
   *
   * @return mixed|NULL
   *   The value or NULL.
   */
  public function __get($name) {
    if ($this->_xml instanceof \SimpleXMLElement && isset($this->_xml->{$name})) {
      return $this->_xml->{$name};
    }

    return NULL;
  }

  /**
   * Magic function to support -> on object.
   *
   * @param string $name
   *   The name of the variable to test.
   *
   * @return bool
   *   Returns TRUE when set, FALSE otherwise.
   */
  public function __isset($name) {
    return $this->_xml instanceof \SimpleXMLElement && isset($this->_xml->{$name});
  }

  /**
   * Call method on $_xml.
   *
   * @param string $name
   *   The name of the method.
   * @param array $arguments
   *   The arguments.
   *
   * @return mixed
   *   Return value depends on called method.
   */
  public function __call($name, $arguments) {
    if ($this->_xml instanceof \SimpleXMLElement && method_exists($this->_xml, $name)) {
      return call_user_func_array(array($this->_xml, $name), $arguments);
    }
  }

  // ---------------------------------------------------------------- Functions.
  /**
   * Check if this response is valid and useful to use.
   *
   * Will return TRUE even if MediaMosa responded with other than 601 (OK).
   *
   * @return bool
   *   Returns TRUE when response is valid and useful, FALSE otherwise.
   */
  public function isResponse() {
    return $this->_xml instanceof \SimpleXMLElement;
  }

  /**
   * Make sure we have the MediaMosa header.
   *
   * @return bool
   *   Returns TRUE when 'header' is available, FALSE otherwise.
   */
  public function hasHeader() {
    return $this->isResponse() && isset($this->header) && !empty($this->header);
  }

  /**
   * Make sure we have the MediaMosa header.
   *
   * @return bool
   *   Returns TRUE when 'header' is available, FALSE otherwise.
   */
  public function hasItems() {
    return $this->_xml instanceof \SimpleXMLElement && isset($this->items) && !empty($this->items);
  }

  /**
   * Check if response resulted in an access denied (not logged in) error.
   * .
   * @return bool
   *   Return TRUE when access was denied (failure) or FALSE when access was
   *   granted.
   */
  public function isAccessDenied() {
    return $this->isResponse() && $this->getHeaderRequestResultID() == MediaMosaSDK::ERRORCODE_ACCESS_DENIED;
  }

  /**
   * Check if response resulted with an access granted.
   * .
   * @return bool
   *   Return TRUE when access was denied (failure) or FALSE when access was
   *   granted.
   */
  public function isAccessGranted() {
    return !$this->isAccessDenied();
  }

  /**
   * Check if response ended with a MediaMosa error.
   *
   * Will also return TRUE when no response or invalid response.
   *
   * @param array $accept_codes
   *   The MediaMosa result codes to accept as not failure.
   *
   * @return bool
   *   Returns TRUE on error, FALSE otherwise.
   */
  public function isError($accept_codes = array(MediaMosaSDK::ERRORCODE_OKAY, MediaMosaSDK::ERRORCODE_EMPTY_RESULT)) {
    return !in_array($this->getHeaderRequestResultID(), $accept_codes);
  }

  /**
   * Return the value(s) of the specific HTTP header.
   *
   * @param string $name
   *   The name of the HTTP header item.
   *
   * @return array|null
   *   Will return array with at least 1 value or NULL.
   */
  public function getHttpHeader($name) {
    $name = Unicode::strtolower($name);
    return isset($this->_http_header[$name]) && count($this->_http_header[$name]) ? $this->_http_header[$name] : NULL;
  }

  /**
   * Return the first value of the specific HTTP header.
   *
   * @param string $name
   *   The name of the HTTP header item.
   *
   * @return string|null
   *   Will return first value found or NULL.
   */
  public function getHttpHeaderFirst($name) {
    $name = Unicode::strtolower($name);
    return isset($this->_http_header[$name]) && count($this->_http_header[$name]) ? $this->_http_header[$name][0] : NULL;

  }

  /**
   * The HTTP headers.
   *
   * @return array
   *   The HTTP headers.
   */
  public function getHttpHeaders() {
    return $this->_http_header;
  }

  /**
   * Store the HTTP header.
   *
   * @param array $header
   *   The HTTP headers to set.
   */
  public function setHttpHeaders(array $header) {
    foreach ($header as $name => $values) {
      $this->_http_header[Unicode::strtolower($name)] = is_array($values) ? array_values($values) : array($values);
    }
  }

  /**
   * Set the response using the data string.
   *
   * @param string $body
   *  The body returned with HTTP.
   * @param string $content_type
   *  The content type, like 'text/xml'.
   */
  public function setResponse($body, $content_type) {
    // Some might contain ; parts.
    if (strpos($content_type, ';') !== FALSE) {
      list($content_type, ) = explode(';', $content_type, 2);
    }

    $content_type = trim(strtolower($content_type));
    if ($content_type === 'text/xml') {
      static::setResponseXML($body);
    }
    else {
      throw new MediaMosaException('Unsupported content type used for body: ' . $content_type);
    }
  }

  /**
   * Response is XML, create the SimpleXML object from it.
   *
   * @param string body
   *   The response body.
   */
  protected function setResponseXML($body) {
    // Replace the namespaces and translate to 2.x version.
    // We need to replace, because SimpleXML does not seem t/ handle this
    // correctly, and also tries to retrieve the XSL each time.
    $data = strtr($body,
      array(
        '<opensearch:itemsPerPage>' => '<item_count>',
        '<opensearch:totalResults>' => '<item_count_total>',
        '<opensearch:startIndex>' => '<item_offset>',
        '</opensearch:itemsPerPage>' => '</item_count>',
        '</opensearch:totalResults>' => '</item_count_total>',
        '</opensearch:startIndex>' => '</item_offset>',
      )
    );

    // Build the simpleXML object from our data.
    $this->_xml = new \SimpleXMLElement($data);

    // Map new header items of 3.x to 2.x.
    if (!empty($this->header->request)) {
      $this->header->request_result = $this->header->request->result;
      $this->header->request_result_id = $this->header->request->resultId;
      $this->header->request_result_description = $this->header->request->resultDescription;

      // Ok, because of SimpleXML and its terrible workings with namespaces, we
      // will need to redo the open-search params.
      $this->header->itemsPerPage = $this->header->item_count;
      $this->header->totalResults = $this->header->item_count_total;
      $this->header->startIndex = $this->header->item_offset;
    }
  }

  /**
   * Returns an rendered string of the response.
   *
   * @return string
   *   The rendered output.
   */
  public function getResponseRendered() {
    return $this->isResponse() ? $this->_xml->asXML() : NULL;
  }

  /**
   * Look if we are version 3 or higher.
   */
  public function mediamosaIsVersion3() {
    $version = $this->mediamosaGetVersion();
    return !MediaMosaSDK::isEmpty($version) && $version[self::VERSION_MAJOR] >= 3;
  }

  /**
   * Use to get version of the MediaMosa server.
   *
   * Return empty version when no version is known.
   *
   * @return array
   *   - 'major': Major version.
   *   - 'minor': Minor version.
   *   - 'release': Release info.
   *   - 'build': build number.
   *   - 'info': Info text.
   */
  public function mediamosaGetVersion() {
    $version = $this->isResponse() ? $this->header->version : '0.0.0.0';

    list($major, $minor, $release, $build, $info) = preg_split("/[.:-]+/", $version, 5) + array(0 => 1, 1 => 0, 2 => 0, 3 => 1, 4 => '');
    return array(
      self::VERSION_MAJOR => (int) $major,
      self::VERSION_MINOR => (int) $minor,
      self::VERSION_RELEASE => (int) $release,
      self::VERSION_BUILD => (int) $build,
      self::VERSION_INFO => $info,
    );
  }

  /**
   * Get the result code from the request.
   *
   * @return integer
   *   The request result ID or 0 when failed.
   */
  public function getHeaderRequestResultID() {
    return $this->hasHeader() && isset($this->header->request_result_id) ? (int) $this->header->request_result_id : 0;
  }

  /**
   * Get the result description.
   *
   * @return string
   *   The request result description or '' when failed.
   */
  public function getHeaderRequestResultDescription() {
    return $this->hasHeader() && isset($this->header->resultDescription) ? (string) $this->header->resultDescription : '';
  }

  /**
   * Return the number of items total in result
   *
   * @return integer
   *   The header item count.
   */
  public function getHeaderItemCountTotal() {
    return $this->hasHeader() && isset($this->header->item_count_total) ? (int) $this->header->item_count_total : 0;
  }

  /**
   * Return the error array when available from response.
   *
   * @return string[]
   *   Return the error array.
   */
  public function getHeaderRequestResultErrors() {
    return $this->hasHeader() && isset($this->header->errors) ? (array) $this->header->errors : array();
  }

  /**
   * Get the MediaMosa header from the response.
   *
   * @return MediaMosaResponseIterator
   *   The MediaMosa header iterator.
   */
  public function getHeader() {
    return $this->hasHeader() ? $this->header : array();
  }

  /**
   * Get the MediaMosa items from the response.
   *
   * @return MediaMosaResponseIterator
   *   The MediaMosa items iterator.
   */
  public function getItems() {
    return $this->hasItems() ? $this->items : array();
  }

}
