<?php

declare(strict_types=1);

namespace CloudLayer\Api;

use CloudLayer\Errors\ValidationException;
use CloudLayer\HttpTransport;
use CloudLayer\Types\StorageDetail;
use CloudLayer\Types\StorageListItem;
use CloudLayer\Types\StorageNotAllowedResponse;
use CloudLayer\Types\StorageParams;
use CloudLayer\Utils\Validator;

/**
 * Storage API methods.
 */
final class Storage
{
    /**
     * @return list<StorageListItem>
     */
    public static function listStorage(HttpTransport $http): array
    {
        $response = $http->get('/storage', ['retryable' => true]);

        if (!is_array($response->data)) {
            return [];
        }

        return array_map(
            static fn (array $item): StorageListItem => StorageListItem::fromArray($item),
            array_values($response->data),
        );
    }

    public static function getStorage(HttpTransport $http, string $storageId): StorageDetail
    {
        if (trim($storageId) === '') {
            throw new ValidationException('storageId', 'must be a non-empty string');
        }

        $response = $http->get("/storage/{$storageId}", ['retryable' => true]);

        /** @var array<string, mixed> $data */
        $data = $response->data;

        return StorageDetail::fromArray($data);
    }

    /**
     * @param StorageParams|array<string, mixed> $config
     */
    public static function addStorage(HttpTransport $http, StorageParams|array $config): StorageDetail|StorageNotAllowedResponse
    {
        Validator::validateStorageParams($config);

        $body = $config instanceof StorageParams ? $config->toArray() : $config;

        $response = $http->post('/storage', $body);

        /** @var array<string, mixed> $data */
        $data = $response->data;

        // Storage not allowed returns { allowed: false, reason: ..., statusCode: ... }
        if (isset($data['allowed']) && $data['allowed'] === false) {
            return StorageNotAllowedResponse::fromArray($data);
        }

        return StorageDetail::fromArray($data);
    }

    public static function deleteStorage(HttpTransport $http, string $storageId): void
    {
        if (trim($storageId) === '') {
            throw new ValidationException('storageId', 'must be a non-empty string');
        }

        $http->delete("/storage/{$storageId}");
    }

    private function __construct()
    {
    }
}
