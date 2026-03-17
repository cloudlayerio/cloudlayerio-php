<?php

declare(strict_types=1);

namespace CloudLayer\Api;

use CloudLayer\Errors\ValidationException;
use CloudLayer\HttpTransport;
use CloudLayer\Types\Asset;

/**
 * Assets API methods.
 */
final class Assets
{
    /**
     * List all assets for the account.
     *
     * WARNING: Returns ALL assets -- no server-side pagination. Each call reads
     * every Firestore asset document.
     *
     * @return list<Asset>
     */
    public static function listAssets(HttpTransport $http): array
    {
        $response = $http->get('/assets', ['retryable' => true]);

        if (!is_array($response->data)) {
            return [];
        }

        return array_map(
            static fn (array $item): Asset => Asset::fromArray($item),
            array_values($response->data),
        );
    }

    public static function getAsset(HttpTransport $http, string $assetId): Asset
    {
        if (trim($assetId) === '') {
            throw new ValidationException('assetId', 'must be a non-empty string');
        }

        $response = $http->get("/assets/{$assetId}", ['retryable' => true]);

        /** @var array<string, mixed> $data */
        $data = $response->data;

        return Asset::fromArray($data);
    }

    private function __construct()
    {
    }
}
