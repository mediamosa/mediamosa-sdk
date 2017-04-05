<?php

namespace Drupal\mediamosa_sdk\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mediamosa_sdk\MediaMosaConnectorInterface;
use Drupal\mediamosa_sdk\MediaMosaSDKService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for MediaMosa Connector.
 */
class MediaMosaConnectorController extends ControllerBase {

  /**
   * The MediaMosa connector service.
   *
   * @var \Drupal\mediamosa_sdk\MediaMosaSDKService;
   */
  protected $service;

  /**
   * Constructs a CronController object.
   *
   * @param \Drupal\mediamosa_sdk\MediaMosaSDKService $service
   *   The MediaMosa SDK service.
   */
  public function __construct(MediaMosaSDKService $service) {
    $this->service = $service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('mediamosa_sdk_service'));
  }

  /**
   * Verify the stored MediaMosa connector.
   *
   * @param string $mediamosa_connector
   *   The name of the MediaMosa connector.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function verify($mediamosa_connector) {
    $connector = $this->service->getConnector($mediamosa_connector);

    if (!($connector instanceof MediaMosaConnectorInterface)) {
      drupal_set_message($this->t('Usable to find and load connector %name.', array('%name' => $mediamosa_connector)), 'error');
    }
    else {
      if ($connector->verify()) {
        drupal_set_message($this->t('Login successful for connector %name.', array('%name' => $mediamosa_connector)));
      }
      else {
        drupal_set_message($this->t('Login failed for connector %name, error message: %message.', array('%name' => $mediamosa_connector, '%message' => $connector->getLastErrorText())), 'error');
      }
    }

    return $this->redirect('entity.mediamosa_connector.collection');
  }

}
