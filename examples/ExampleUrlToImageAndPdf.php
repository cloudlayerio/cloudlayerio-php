<?php
require __DIR__ . '/app.php';

use CloudLayerIO\Resources\Url;

$url = new Url('http://example.com');

try {

    //convert to image
    $file = $url->toImage();
    $file->save(__DIR__ . '/storage/url-example.png');

    //convert to pdf
    $file = $url->toPdf();
    $file->save(__DIR__ . '/storage/url-example.pdf');

} catch (\CloudLayerIO\Exceptions\UnauthorizedUsage $exception) {
    echo $exception->getMessage();
}


