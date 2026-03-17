<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use CloudLayer\CloudLayer;

$apiKey = getenv('CLOUDLAYER_API_KEY') ?: throw new RuntimeException('Set CLOUDLAYER_API_KEY');

$client = new CloudLayer(apiKey: $apiKey, apiVersion: 'v2');

// Render a template with data
$result = $client->templateToPdf([
    'templateId' => 'your-template-id',
    'data' => [
        'invoiceNumber' => 'INV-001',
        'customerName' => 'Jane Smith',
        'items' => [
            ['name' => 'Widget', 'qty' => 3, 'price' => 9.99],
            ['name' => 'Gadget', 'qty' => 1, 'price' => 24.99],
        ],
        'total' => 54.96,
    ],
]);

$job = $client->waitForJob($result->data->id);
$pdfBytes = $client->downloadJobResult($job);
file_put_contents('invoice.pdf', $pdfBytes);
echo "Invoice saved\n";
