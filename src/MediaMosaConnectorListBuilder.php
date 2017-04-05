<?php

namespace Drupal\mediamosa_sdk;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\mediamosa_sdk\Entity\MediaMosaConnector;

/**
 * Defines a class to build a listing of MediaMosa Connector entities.
 *
 * @see \Drupal\mediamosa_sdk\Entity\MediaMosaConnector
 */
class MediaMosaConnectorListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Connector name');
    $header['name'] = $this->t('Connector ID');
    $header['url'] = $this->t('Url');
    $header['verbose'] = $this->t('Verbose');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity MediaMosaConnector */
    $row['label'] = $entity->label();
    $row['name'] = $entity->getName();
    $row['url'] = $entity->getURL();
    $row['verbose'] = $entity->getVerbose() ? $this->t('Yes') : $this->t('No');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('There are currently no connectors. <a href=":url">Add a new one</a>.', [
      ':url' => Url::fromRoute('mediamosa_sdk.connector_add')->toString(),
    ]);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    return parent::getDefaultOperations($entity) + array(
      'verify' => array(
        'title' => t('Verify connection'),
        'weight' => 200,
        'url' => $entity->urlInfo('verify'),
      ),
    );
  }
}
