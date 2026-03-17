<?php

declare(strict_types=1);

namespace CloudLayer\Api;

use CloudLayer\Errors\ValidationException;
use CloudLayer\HttpTransport;
use CloudLayer\Types\PublicTemplate;

/**
 * Templates API methods (public, always v2 path).
 */
final class Templates
{
    /**
     * @param ?array<string, mixed> $options Filter options (type, category, tags, expand)
     * @return list<PublicTemplate>
     */
    public static function listTemplates(HttpTransport $http, ?array $options = null): array
    {
        $requestOptions = [
            'retryable' => true,
            'absolutePath' => true,
        ];

        if ($options !== null) {
            $params = array_filter($options, static fn (mixed $v): bool => $v !== null);
            if ($params !== []) {
                $requestOptions['params'] = $params;
            }
        }

        $response = $http->get('/v2/templates', $requestOptions);

        if (!is_array($response->data)) {
            return [];
        }

        return array_map(
            static fn (array $item): PublicTemplate => PublicTemplate::fromArray($item),
            array_values($response->data),
        );
    }

    public static function getTemplate(HttpTransport $http, string $templateId): PublicTemplate
    {
        if (trim($templateId) === '') {
            throw new ValidationException('templateId', 'must be a non-empty string');
        }

        $response = $http->get("/v2/template/{$templateId}", [
            'retryable' => true,
            'absolutePath' => true,
        ]);

        /** @var array<string, mixed> $data */
        $data = $response->data;

        return PublicTemplate::fromArray($data);
    }

    private function __construct()
    {
    }
}
