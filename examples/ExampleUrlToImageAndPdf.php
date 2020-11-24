<?php

use CloudLayerIO\Resources\Url;

require __DIR__ . '/app.php';


$options = [
    //options
    'format' => 'A4',
    'margin' => [
        'top' => '156px',
    ],
];


try {

    //convert to image
    $url = new Url('http://example.com', $options);
    $file = $url->toImage();
    $file->save(__DIR__ . '/storage/url-example.png');

    //convert to pdf
    $file = $url->toPdf();
    $url = new Url('http://example.com');
    $file->save(__DIR__ . '/storage/url-example.pdf');

} catch (\CloudLayerIO\Exceptions\UnauthorizedUsage $exception) {
    echo $exception->getMessage();
} catch (\GuzzleHttp\Exception\ServerException $e) {
    echo $e->getMessage();
}

