<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use CloudLayer\CloudLayer;

// Set your API key via environment variable
$apiKey = getenv('CLOUDLAYER_API_KEY') ?: throw new RuntimeException('Set CLOUDLAYER_API_KEY');

$client = new CloudLayer(apiKey: $apiKey, apiVersion: 'v2');

// Convert a URL to PDF
$result = $client->urlToPdf([
    'url' => 'https://example.com',
    'printBackground' => true,
    'format' => 'a4',
    'margin' => ['top' => '1cm', 'bottom' => '1cm'],
]);

// v2 returns a Job — poll until complete
$job = $client->waitForJob($result->data->id);
echo "Job completed: {$job->id}\n";

// Download the PDF
$pdfBytes = $client->downloadJobResult($job);
file_put_contents('output.pdf', $pdfBytes);
echo "Saved to output.pdf (" . strlen($pdfBytes) . " bytes)\n";
