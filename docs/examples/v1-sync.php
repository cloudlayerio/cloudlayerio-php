<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use CloudLayer\CloudLayer;

$apiKey = getenv('CLOUDLAYER_API_KEY') ?: throw new RuntimeException('Set CLOUDLAYER_API_KEY');

// v1 returns binary directly — no job polling needed
$client = new CloudLayer(apiKey: $apiKey, apiVersion: 'v1');

$result = $client->urlToPdf(['url' => 'https://example.com']);

// v1 sync: $result->data is the raw PDF binary
file_put_contents('output.pdf', $result->data);
echo "Saved PDF (" . strlen($result->data) . " bytes)\n";

// Response headers available
echo "Job ID: {$result->headers->workerJobId}\n";
echo "Process time: {$result->headers->processTime}ms\n";
