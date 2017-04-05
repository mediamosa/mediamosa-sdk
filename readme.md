MediaMosa Software Development Kit for Drupal 8 (alpha)
=======================================================

The MediaMosa SDK has been developed for Drupal 6, 7 and 8. These PHP API tools will enable developers to connect 
front-end applications to the back-end REST interface of MediaMosa.

The structure and file names for Drupal 8 has been changed significantly compared to 6 and 7 versions. Please note the
changes below.

- The mediamosa_sdk class has been renamed to Drupal\mediamosa_sdk\MediaMosaSDK. File name has been renamed to 
MediaMosaSDK.php.
- The SDK contains the Drupal service class MediaMosaSDKService.
- In this version you can add multiple connectors with different IDs (machine names).
- Connector setup is exported and imported using the Drupal config.

Security notice
---------------

As the connector information is exported using Drupal config, please take note that the shared key information is also
exported and is clearly readable in export yml files.

Usage
-----

Enable MediaMosa SDK Drupal 8 module.

Add connector using the interface on /admin/config/media/mediamosa-connector. You can add different connectors for
different MediaMosa back-ends. As these connectors are also exported with Drupal config, is good idea to create 
different connectors for each development stage, e.g. staging_connector, accept_connector and production_connector. In 
code you can choose the connector based on your environment. You can even add multiple connectors using the same login
information, but using different REST back-end URLs. The machine name (Connector ID) uniquely identifies the connector.

Enabling verbose also requires to enabe the verbose MediaMosa block. You can do this on the block page in Drupal 
(/admin/structure/block -> place block).

When adding the connector in Drupal, enter the machine name as the unique ID of the connector. You must use the machine 
name (CONNECTOR ID column in listing) to create the connector object in PHP;

```php
use Drupal\mediamosa_sdk\MediaMosaSDKService;

$mediamosa_connector = MediaMosaSDKService::getService()->getConnector('accept_connector');
```

Note that $mediamosa_connector can be NULL when connector was not found.

```php
if ($mediamosa_connector instanceof MediaMosaConnector) {
  ...
}
else {
  // Unable to get connector, check settings, log or display message to user.
}
```

Calling REST, e.g. '/asset' (listing of assets).

```php
use Drupal\mediamosa_sdk\Entity\MediaMosaConnector;

$mediamosa_connector = MediaMosaSDKService::getService()->getConnector('test');
if ($mediamosa_connector instanceof MediaMosaConnector) {
  try {
    $response = $mediamosa_connector->requestGet('asset');
    
    if ($response->isError()) {
      // Act on MediaMosa errors.
    }
    
    foreach ($response->getItems() as $item) {
      // The items in response.
    }

  }
  catch (MediaMosaConnectorExceptionFailedLogin $e) {
    // Failed connector login.
  }
  catch (MediaMosaConnectorExceptionInvalidResponse $e) {
    // Got invalid response.
  }
  catch (MediaMosaConnectorExceptionNotSetup $e) {
    // Connector is incorrect.
  }
  catch (MediaMosaException $e) {
    // Other Exception.
    // You can use $e->getPrevious() for catched Exceptions that where
    // caught by connector that where other type than MediaMosaException.
  }
}
else {
  // No connector found, check settings, log and or display message to user.
}
```

Notes about the MediaMosaResponse class
---------------------------------------

Although this class will return SimpleXMLIterator on XML responses and will act as an SimpleXMLElement, do not assume 
that this will continue in the future. It will return object compatible with MediaMosaResponseIterator class that we
will extend in the future. 

The response class allows you to iterate as array and retrieve data using -> operator, e.g. $items[0]->item->asset_id.
Do not assume string is returned on data either, its good practise to cast values to string;
```php
$asset_id = (string) $items[0]->item->asset_id
```

The convertor function for XML to array has not been ported. This was already deprecated in Drupal 7 version.
