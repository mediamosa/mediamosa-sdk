<?php

namespace Drupal\mediamosa_sdk\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Controller for MediaMosa connector addition forms.
 */
class MediaMosaConnectorAddForm extends MediaMosaConnectorFormBase {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    drupal_set_message($this->t('MediaMosa connector %name was created.', array('%name' => $this->entity->label())));
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Create new MediaMosa connector');

    return $actions;
  }

}
