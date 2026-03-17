# cloudlayer.io PHP SDK

[![Packagist Version](https://img.shields.io/packagist/v/cloudlayerio/cloudlayerio-php)](https://packagist.org/packages/cloudlayerio/cloudlayerio-php)
[![PHP Version](https://img.shields.io/packagist/dependency-v/cloudlayerio/cloudlayerio-php/php)](https://packagist.org/packages/cloudlayerio/cloudlayerio-php)
[![License](https://img.shields.io/packagist/l/cloudlayerio/cloudlayerio-php)](LICENSE)
[![CI](https://github.com/cloudlayerio/cloudlayerio-php/actions/workflows/ci.yml/badge.svg)](https://github.com/cloudlayerio/cloudlayerio-php/actions/workflows/ci.yml)

Official PHP SDK for the [cloudlayer.io](https://cloudlayer.io) document generation API.

## Installation

```bash
composer require cloudlayerio/sdk
```

## Quick Start

```php
<?php

use CloudLayer\CloudLayer;

$client = new CloudLayer(
    apiKey: 'your-api-key',
    apiVersion: 'v2',
);

// Convert a URL to PDF
$result = $client->urlToPdf(['url' => 'https://example.com']);

// v2 returns a Job — poll until complete, then download
$job = $client->waitForJob($result->data->id);
$pdfBytes = $client->downloadJobResult($job);

file_put_contents('output.pdf', $pdfBytes);
```

## Requirements

- PHP >= 8.1
- ext-json

## API Version Differences

| Aspect | v1 | v2 |
|--------|----|----|
| Default mode | Synchronous | Asynchronous |
| Sync response | Raw binary (PDF/image bytes) | JSON Job object |
| Async response | JSON Job object | JSON Job object |
| Binary access | Direct from response | `downloadJobResult($job)` |

### v1 Example

```php
$client = new CloudLayer(apiKey: 'key', apiVersion: 'v1');

$result = $client->urlToPdf(['url' => 'https://example.com']);
// v1 sync: $result->data is the raw PDF binary
file_put_contents('output.pdf', $result->data);
```

### v2 Example

```php
$client = new CloudLayer(apiKey: 'key', apiVersion: 'v2');

$result = $client->urlToPdf(['url' => 'https://example.com']);
// v2: $result->data is a Job object
$job = $client->waitForJob($result->data->id);
$pdfBytes = $client->downloadJobResult($job);
file_put_contents('output.pdf', $pdfBytes);
```

## Configuration

```php
$client = new CloudLayer(
    apiKey: 'your-api-key',       // Required
    apiVersion: 'v2',             // Required: 'v1' or 'v2'
    baseUrl: 'https://api.cloudlayer.io', // Default
    timeout: 30000,               // Request timeout in ms (default: 30000)
    maxRetries: 2,                // Retries for data endpoints (default: 2, max: 5)
    headers: [],                  // Additional HTTP headers
);
```

## Conversion Methods

### URL to PDF / Image

```php
$result = $client->urlToPdf([
    'url' => 'https://example.com',
    'printBackground' => true,
    'format' => 'a4',
    'margin' => ['top' => '1cm', 'bottom' => '1cm'],
]);

$result = $client->urlToImage([
    'url' => 'https://example.com',
    'imageType' => 'png',
    'quality' => 90,
]);
```

### HTML to PDF / Image

HTML content must be Base64-encoded:

```php
$html = base64_encode('<html><body><h1>Hello</h1></body></html>');

$result = $client->htmlToPdf(['html' => $html]);
$result = $client->htmlToImage(['html' => $html, 'imageType' => 'png']);
```

### Template Rendering

```php
// By template ID
$result = $client->templateToPdf([
    'templateId' => 'your-template-id',
    'data' => ['name' => 'John', 'amount' => 99.99],
]);

// By raw template (Base64-encoded)
$result = $client->templateToPdf([
    'template' => base64_encode('<h1>{{name}}</h1>'),
    'data' => ['name' => 'John'],
]);
```

### File Conversion

```php
// DOCX to PDF (file path)
$result = $client->docxToPdf(['file' => '/path/to/document.docx']);

// PDF to DOCX (raw content)
$result = $client->pdfToDocx(['file' => file_get_contents('input.pdf')]);

// DOCX to HTML
$result = $client->docxToHtml(['file' => '/path/to/document.docx']);
```

### PDF Merge

```php
$result = $client->mergePdfs([
    'url' => 'https://example.com/doc1.pdf',
]);
```

### Batch Processing

```php
$result = $client->urlToPdf([
    'batch' => ['urls' => [
        'https://example.com/page1',
        'https://example.com/page2',
        'https://example.com/page3',
    ]],
]);
```

## Async Mode & Job Polling

```php
// v2 defaults to async
$result = $client->urlToPdf(['url' => 'https://example.com']);

// Poll until the job completes (default: 5s interval, 5min timeout)
$job = $client->waitForJob($result->data->id, [
    'interval' => 5000,   // Poll every 5 seconds (minimum: 2000ms)
    'maxWait' => 300000,  // Give up after 5 minutes
]);

// Download the result
$binary = $client->downloadJobResult($job);
```

## Data Management

```php
// Jobs (WARNING: listJobs returns ALL jobs — no pagination)
$jobs = $client->listJobs();
$job = $client->getJob('job-id');

// Assets (WARNING: listAssets returns ALL assets — no pagination)
$assets = $client->listAssets();
$asset = $client->getAsset('asset-id');

// Storage
$storageList = $client->listStorage();
$storage = $client->getStorage('storage-id');
$result = $client->addStorage(new \CloudLayer\Types\StorageParams(
    title: 'My S3 Bucket',
    region: 'us-east-1',
    accessKeyId: 'AKIA...',
    secretAccessKey: 'secret',
    bucket: 'my-bucket',
));
$client->deleteStorage('storage-id');

// Account
$account = $client->getAccount();
$status = $client->getStatus();

// Public Templates (always v2, no auth required)
$templates = $client->listTemplates(['type' => 'invoice']);
$template = $client->getTemplate('template-id');
```

## Error Handling

```php
use CloudLayer\Errors\CloudLayerException;
use CloudLayer\Errors\ConfigException;
use CloudLayer\Errors\ValidationException;
use CloudLayer\Errors\AuthException;
use CloudLayer\Errors\RateLimitException;
use CloudLayer\Errors\ApiException;
use CloudLayer\Errors\TimeoutException;
use CloudLayer\Errors\NetworkException;

try {
    $result = $client->urlToPdf(['url' => 'https://example.com']);
} catch (AuthException $e) {
    // 401/403 — invalid or missing API key
    echo "Auth error: {$e->getMessage()}\n";
} catch (RateLimitException $e) {
    // 429 — rate limited, retry after $e->retryAfter seconds
    echo "Rate limited. Retry after {$e->retryAfter}s\n";
} catch (TimeoutException $e) {
    // Request timed out
    echo "Timeout after {$e->timeout}ms\n";
} catch (NetworkException $e) {
    // DNS failure, connection refused, etc.
    echo "Network error: {$e->getMessage()}\n";
} catch (ApiException $e) {
    // Other API errors (4xx/5xx)
    echo "API error {$e->status}: {$e->getMessage()}\n";
} catch (ValidationException $e) {
    // Client-side validation failure
    echo "Invalid {$e->field}: {$e->getMessage()}\n";
} catch (ConfigException $e) {
    // Invalid client configuration
    echo "Config error: {$e->getMessage()}\n";
}
```

## Performance Notes

- **`listJobs()` / `listAssets()`**: Return ALL records with no pagination. Each call reads every document from the database. Use `getJob()` / `getAsset()` for single lookups.
- **`waitForJob()` polling**: Each poll reads one database document. Default 5-second interval is intentional — don't decrease below 2 seconds.
- **Conversion requests are not retried**: Conversions are expensive server-side operations. Only data management GET endpoints are automatically retried on 429/5xx.

## Type Safety

The SDK uses `declare(strict_types=1)` in every file and passes PHPStan level 8 (maximum strictness). All response types are readonly value objects with typed properties.

## Other SDKs

- **JavaScript/TypeScript:** [@cloudlayerio/sdk](https://www.npmjs.com/package/@cloudlayerio/sdk) ([GitHub](https://github.com/cloudlayerio/cloudlayerio-js))
- **Python:** [cloudlayerio](https://pypi.org/project/cloudlayerio/) ([GitHub](https://github.com/cloudlayerio/cloudlayerio-python))
- **Go:** [cloudlayerio-go](https://pkg.go.dev/github.com/cloudlayerio/cloudlayerio-go) ([GitHub](https://github.com/cloudlayerio/cloudlayerio-go))
- **.NET C#:** [cloudlayerio-dotnet](https://www.nuget.org/packages/cloudlayerio-dotnet/) ([GitHub](https://github.com/cloudlayerio/cloudlayerio-dotnet))

## License

[Apache-2.0](LICENSE)
