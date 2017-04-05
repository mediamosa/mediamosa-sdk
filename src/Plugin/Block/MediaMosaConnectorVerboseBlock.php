<?php

namespace Drupal\mediamosa_sdk\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides MediaMosa Connector verbose debug block.
 *
 * @Block(
 * id = "mediamosa_connector_verbose_block",
 * admin_label = @Translation("MediaMosa Connector Verbose"),
 * category = @Translation("MediaMosa")
 * )
 */
class MediaMosaConnectorVerboseBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\mediamosa_sdk\Form\MediaMosaConnectorVerboseForm');
  }

}
