<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use CloudLayer\CloudLayer;
use CloudLayer\Types\StorageDetail;
use CloudLayer\Types\StorageNotAllowedResponse;
use CloudLayer\Types\StorageParams;

$apiKey = getenv('CLOUDLAYER_API_KEY') ?: throw new RuntimeException('Set CLOUDLAYER_API_KEY');

$client = new CloudLayer(apiKey: $apiKey, apiVersion: 'v2');

// List storage configs
$storageList = $client->listStorage();
foreach ($storageList as $item) {
    echo "Storage: {$item->id} — {$item->title}\n";
}

// Add a new storage config
$result = $client->addStorage(new StorageParams(
    title: 'My S3 Bucket',
    region: 'us-east-1',
    accessKeyId: 'AKIA...',
    secretAccessKey: 'your-secret',
    bucket: 'my-pdf-bucket',
));

if ($result instanceof StorageNotAllowedResponse) {
    echo "Storage not allowed: {$result->reason}\n";
} elseif ($result instanceof StorageDetail) {
    echo "Storage created: {$result->id}\n";

    // Get storage detail
    $detail = $client->getStorage($result->id);
    echo "Storage: {$detail->title}\n";

    // Delete storage
    $client->deleteStorage($result->id);
    echo "Storage deleted\n";
}
