<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use CloudLayer\CloudLayer;

$apiKey = getenv('CLOUDLAYER_API_KEY') ?: throw new RuntimeException('Set CLOUDLAYER_API_KEY');

$client = new CloudLayer(apiKey: $apiKey, apiVersion: 'v2');

// HTML must be Base64-encoded
$html = base64_encode('<html><body style="background:#f0f0f0"><h1>Hello World</h1></body></html>');

$result = $client->htmlToImage([
    'html' => $html,
    'imageType' => 'png',
    'quality' => 90,
]);

$job = $client->waitForJob($result->data->id);
$imageBytes = $client->downloadJobResult($job);
file_put_contents('output.png', $imageBytes);
echo "Saved to output.png\n";
