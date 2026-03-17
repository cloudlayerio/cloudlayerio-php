<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use CloudLayer\CloudLayer;

$apiKey = getenv('CLOUDLAYER_API_KEY') ?: throw new RuntimeException('Set CLOUDLAYER_API_KEY');

$client = new CloudLayer(apiKey: $apiKey, apiVersion: 'v2');

// Batch convert multiple URLs to PDF (max 20)
$result = $client->urlToPdf([
    'batch' => ['urls' => [
        'https://example.com/page1',
        'https://example.com/page2',
        'https://example.com/page3',
    ]],
]);

$job = $client->waitForJob($result->data->id);
echo "Batch job completed: {$job->id}\n";
