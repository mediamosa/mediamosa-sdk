<?php

namespace Drupal\mediamosa_sdk;

/**
 * Class MediaMosaResponseIterator
 */
class MediaMosaResponseIterator implements Traversable, RecursiveIterator, Iterator, Countable {
  /**
   * Rewind to the first element
   */
  public function rewind () {}

  /**
   * Check whether the current element is valid.
   *
   * @return bool
   *   TRUE if the current element is valid, otherwise FALSE.
   */
  public function valid () {}

  /**
   * Returns the current element.
   *
   * @return mixed
   *   The current element as a MediaMosaResponseIterator object or NULL on
   *   failure.
   */
  public function current () {}

  /**
   * Return current key.
   *
   * @return mixed
   *   The name of the element referenced by the current
   *   MediaMosaResponseIterator object or FALSE.
   */
  public function key () {}

  /**
   * Move to next element.
   */
  public function next () {}

  /**
   * Checks whether the current element has sub elements.
   *
   * @return bool
   *   TRUE if the current element has sub-elements, otherwise FALSE
   */
  public function hasChildren () {}

  /**
   * Returns the sub-elements of the current element.
   *
   * @return SimpleXMLIterator
   *   A SimpleXMLIterator< object containing the sub-elements of the current
   *   element.
   */
  public function getChildren () {}

  /**
   * Returns the string content
   *
   * @return string
   *   The string content on success or an empty string on failure.
   */
  public function __toString () {}

  /**
   * Counts the children of an element
   *
   * @return int
   *   The number of elements of an element.
   */
  public function count () {}
}
