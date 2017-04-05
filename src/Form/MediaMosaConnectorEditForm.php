<?php

namespace Drupal\mediamosa_sdk\Form;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for MediaMosa connector edit form.
 */
class MediaMosaConnectorEditForm extends MediaMosaConnectorFormBase {

  /**
   * Constructs an MediaMosaConnectorEditForm object.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $mediamosa_connector_storage
   *   The storage.
   */
  public function __construct(EntityStorageInterface $mediamosa_connector_storage) {
    parent::__construct($mediamosa_connector_storage);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('mediamosa_connector')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    drupal_set_message($this->t('Changes to the connector have been saved.'));
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Update connector');

    return $actions;
  }

}
