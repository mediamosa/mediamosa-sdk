<?php
/**
 * @file
 * Helper class to support output format of MediaMosa REST XML.
 *
 * Specificly extended on the asset REST call.
 */
class mediamosa_connector_response_asset extends mediamosa_connector_response {
  // -------------------------------------------------------------- Constructor.
  /**
   * Basic constructor.
   *
   * @todo
   * Needs rewrite as mediamosa_connector will accept any response class.
   *
   * @param mediamosa_connector_response $mediamosa_connector_response
   *   The mediamosa_connector_response object.
   */
  public function __construct($mediamosa_connector_response) {
    $this->xml = $mediamosa_connector_response->xml;
    //parent::__construct($mediamosa_connector_response->xml->asXML());
  }

  // ------------------------------------------------------------------ Methods.
  /**
   * Retrieve the (first) asset from the XML output
   *
   * @return array
   */
  public function get_asset() {
    $asset = $this->xml->xpath('items/item');
    return reset($asset);
  }

  /**
   * Get the ID of the asset.
   *
   * @return string
   *   The ID of the asset.
   */
  public function get_asset_id() {
   return empty($this->xml->items->item->asset_id) ? FALSE : (string) $this->xml->items->item->asset_id;
  }

  /**
   * Get the owner of the asset.
   *
   * @return string
   *   The owner of the asset.
   */
  public function get_owner() {
   return empty($this->xml->items->item->owner_id) ? FALSE : (string) $this->xml->items->item->owner_id;
  }

  /**
   * Get IDs all of original mediafiles.
   *
   * @return array
   *   Array of original mediafile IDs.
   */
  public function get_original_mediafiles() {
    $mediafiles = array();
    foreach ($this->xml->xpath('items/item/mediafiles/mediafile') as $mediafile) {
      if (!MediaMosaCkConnectorWrapper::string2bool($mediafile->is_original_file)) {
        continue;
      }
      $mediafiles[(string) $mediafile->mediafile_id] = $mediafile;
    }
    return $mediafiles;
  }
}
