<?php
require __DIR__ . '/app.php';

use CloudLayerIO\Resources\Html;

$html = new Html('<html><body><h1>Test Title</h1><p>Paragraph</p></body></html>');


try {

    //convert to image
    $file = $html->toImage();
    $file->save(__DIR__ . '/storage/html-example.png');

    //convert to pdf
    $file = $html->toPdf();
    $file->save(__DIR__ . '/storage/html-example.pdf');

} catch (\CloudLayerIO\Exceptions\UnauthorizedUsage $exception) {
    echo $exception->getMessage();
}


