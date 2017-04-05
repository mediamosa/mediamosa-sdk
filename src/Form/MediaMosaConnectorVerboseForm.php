<?php

namespace Drupal\mediamosa_sdk\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mediamosa_sdk\Entity\MediaMosaConnector;
use Drupal\mediamosa_sdk\MediaMosaSDKService;

/**
 * Renders the verbose log using an form.
 */
class MediaMosaConnectorVerboseForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'verbose_log_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $verbose_logs = MediaMosaSDKService::getService()->getVerboseLog(TRUE);

    $form = array();
    foreach ($verbose_logs as $connector => $connector_logs) {
      $mediamosa_connector = MediaMosaSDKService::getService()->getConnector($connector);

      $form['log'][$connector] = array(
        '#type' => 'details',
        '#title' => $this->formatPlural(count($connector_logs), '1 call made with connector \'%connector\'.', '@count calls made with connector \'%connector\'.', array('%connector' => $mediamosa_connector instanceof MediaMosaConnector ? $mediamosa_connector->label() : $connector)),
        '#open' => FALSE,
      );

      foreach ($connector_logs as $key => $log) {
        $form['log'][$connector]['tab'][$key] = array(
          '#type' => 'details',
          '#title' => '[' . $log['method'] . '] ' . $log['uri'] . ' (' . $log['result_id'] . (empty($log['result_description']) ? '' : ': ' . $log['result_description']) . '), (' . $log['response_time'] . ' sec)',
          '#open' => FALSE,
        );
        $form['log'][$connector]['tab'][$key]['data'] = array(
          '#prefix' => '<pre style="font-family:Fixed, monospace;font-size:12px;"><div align="left" class="mediamosa-connector-data">',
          '#suffix' => '</div></pre>',
          '#markup' => highlight_string($log['data'], TRUE),
        );
      }
    }

    if (empty($verbose_logs)) {
      $form['log']['#markup'] = 'No calls where made or logged.';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
