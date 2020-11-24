<?php

use CloudLayerIO\Resources\Html;

require __DIR__ . '/app.php';


$htmlString = '<html><body><h1 class="page-header">Test Title</h1><p>Paragraph</p></body></html>';
$options = [
    //options
    'format' => 'A4',
    'margin' => [
        'top' => '156px',
    ],
    'headerTemplate' => [
        'method' => 'extract',
        'selector' => '.page-header',
        'margin' => [
            'bottom' => '10px',
        ],
        'imageStyle' => [
            'padding-bottom' => '10px',
            'height' => '52px',
        ],
        'style' => [
            'width' => '100%',
            'border-top' => '2px solid #354ca1',
            'border-bottom' => '2px solid #354ca1',
        ],
    ],
];




try {

    //convert to image
    $html = new Html($htmlString, $options);
    $file = $html->toImage();
    $file->save(__DIR__ . '/storage/html-example.png');

    //convert to pdf
    $html = new Html($htmlString);
    $file = $html->toPdf();
    $file->save(__DIR__ . '/storage/html-example.pdf');

} catch (\CloudLayerIO\Exceptions\UnauthorizedUsage $exception) {
    echo $exception->getMessage();
} catch ( \GuzzleHttp\Exception\ServerException $e){
    echo $e->getMessage();
}
