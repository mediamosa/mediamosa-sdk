<?php

namespace Drupal\mediamosa_sdk;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a MediaMosa connector entity.
 */
interface MediaMosaConnectorInterface extends ConfigEntityInterface {

  /**
   * Returns the MediaMosa REST interface url.
   *
   * @return string
   *   The the MediaMosa REST interface url.
   */
  public function getURL();

  /**
   * Returns the client application name.
   *
   * @return string
   *   The name of the client application.
   */
  public function getSharedKey();

  /**
   * Returns verbose debug setting.
   *
   * @return string
   *   Verbose the MediaMosa connection information.
   */
  public function getVerbose();

  /**
   * Check the current connection using the stored client application information.
   *
   * @return bool
   *   Returns TRUE when verify, FALSE otherwise.
   */
  public function verify();
}
