<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use CloudLayer\CloudLayer;
use CloudLayer\Errors\ApiException;
use CloudLayer\Errors\AuthException;
use CloudLayer\Errors\ConfigException;
use CloudLayer\Errors\NetworkException;
use CloudLayer\Errors\RateLimitException;
use CloudLayer\Errors\TimeoutException;
use CloudLayer\Errors\ValidationException;

$apiKey = getenv('CLOUDLAYER_API_KEY') ?: throw new RuntimeException('Set CLOUDLAYER_API_KEY');

$client = new CloudLayer(apiKey: $apiKey, apiVersion: 'v2');

try {
    $result = $client->urlToPdf(['url' => 'https://example.com']);
    $job = $client->waitForJob($result->data->id);
    $pdf = $client->downloadJobResult($job);
    echo "Success: " . strlen($pdf) . " bytes\n";
} catch (AuthException $e) {
    echo "Authentication error ({$e->status}): {$e->getMessage()}\n";
} catch (RateLimitException $e) {
    echo "Rate limited. Retry after: {$e->retryAfter}s\n";
} catch (TimeoutException $e) {
    echo "Timed out after {$e->timeout}ms on {$e->requestMethod} {$e->requestPath}\n";
} catch (NetworkException $e) {
    echo "Network error: {$e->getMessage()}\n";
} catch (ApiException $e) {
    echo "API error {$e->status}: {$e->getMessage()}\n";
    // Optionally parse structured error body
    $errorBody = $e->getErrorBody();
    if ($errorBody !== null) {
        echo "  Details: {$errorBody->message}\n";
    }
} catch (ValidationException $e) {
    echo "Validation error on '{$e->field}': {$e->getMessage()}\n";
} catch (ConfigException $e) {
    echo "Configuration error: {$e->getMessage()}\n";
}
