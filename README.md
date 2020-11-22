## Status: Pending
### The work on this SDK is pending, in the meantime you can visit https://cloudlayer.io/docs to view our REST based API.

# cloudlayerio-php
cloudlayerio API Library for easy access to our REST based API services using PHP.


## Example Usage
- load composer dependencies
```
require __DIR__ ."/vendor/autoload.php";
```
- configure a client with api key
```

use CloudLayerIO\Client as CloudLayer;

$apiKey = '<YOUR_API_KEY>';

CloudLayer::config([
    'api_key' => $apiKey,
]);
```
- convert URL to image or pdf
```
$url = \CloudLayerIO\Resource\Url('http://exampl.com');
$image = $url->toImage(); // return object of \CloudLayerIO\Model\File

//save image file
$image->save('test.png');

$pdf = $url->toPdf(); // return object of \CloudLayerIO\Model\File
//save pdf file
$pdf->save('test.pdf');
```
- convert HTML to image or pdf
```
$html = \CloudLayerIO\Resource\Html('<html></html>');
$html->toImage(); // return object of \CloudLayerIO\Model\File
$html->toPdf(); // return object of \CloudLayerIO\Model\File
```