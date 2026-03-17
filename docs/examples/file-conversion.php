<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use CloudLayer\CloudLayer;

$apiKey = getenv('CLOUDLAYER_API_KEY') ?: throw new RuntimeException('Set CLOUDLAYER_API_KEY');

$client = new CloudLayer(apiKey: $apiKey, apiVersion: 'v2');

// Convert DOCX to PDF by file path
$result = $client->docxToPdf(['file' => '/path/to/document.docx']);

$job = $client->waitForJob($result->data->id);
$pdfBytes = $client->downloadJobResult($job);
file_put_contents('document.pdf', $pdfBytes);
echo "Converted DOCX to PDF\n";
