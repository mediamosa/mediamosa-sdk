<?php

namespace Drupal\mediamosa_sdk\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form for MediaMosa connector add and edit forms.
 */
abstract class MediaMosaConnectorFormBase extends EntityForm {

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\mediamosa_sdk\MediaMosaConnectorInterface
   */
  protected $entity;

  /**
   * The MediaMosa connector entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $MediaMosaConnectorStorage;

  /**
   * Constructs a base class for MediaMosa connector add and edit forms.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $mediamosa_connector_storage
   *   The MediaMosa connector entity storage.
   */
  public function __construct(EntityStorageInterface $mediamosa_connector_storage) {
    $this->MediaMosaConnectorStorage = $mediamosa_connector_storage;
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
  public function form(array $form, FormStateInterface $form_state) {

    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $this->entity->getURL(),
      '#maxlength' => 255,
      '#size' => 45,
      '#description' => $this->t('Enter the URL of the REST interface you want to connect to.'),
      '#required' => TRUE,
    );

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Client application name'),
      '#default_value' => $this->entity->label(),
      '#description' => $this->t('The unique client application name specified for this connection.'),
      '#required' => TRUE,
    );
    $form['name'] = array(
      '#type' => 'machine_name',
      '#machine_name' => array(
        'exists' => array($this->MediaMosaConnectorStorage, 'load'),
      ),
      '#default_value' => $this->entity->id(),
      '#required' => TRUE,
    );

    $form['shared_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Client application shared key'),
      '#default_value' => $this->entity->getSharedKey(),
      '#maxlength' => 255,
      '#size' => 45,
      '#description' => $this->t('The shared key used for authentication of the client application.'),
      '#required' => TRUE,
    );

    $form['verbose'] = array(
      '#type' => 'checkbox',
      '#title' => t('Verbose MediaMosa request information'),
      '#default_value' => $this->entity->getVerbose(),
      '#description' => t('Enable to display debug request information. This information is shown in a Drupal block. You must enable the MediaMosa verbose debug block to view this information.'),
    );

    return parent::form($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!UrlHelper::isValid($form_state->getValue('url'), TRUE)) {
      $form_state->setErrorByName('url', $this->t('Enter a valid URL.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $form_state->setRedirectUrl($this->entity->urlInfo('collection'));
  }

}
