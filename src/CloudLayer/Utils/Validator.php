<?php

declare(strict_types=1);

namespace CloudLayer\Utils;

use CloudLayer\Errors\ValidationException;
use CloudLayer\Types\StorageParams;

/**
 * Client-side input validation before sending requests.
 */
final class Validator
{
    /**
     * @param array<string, mixed> $options
     */
    public static function validateBaseOptions(array $options): void
    {
        if (isset($options['timeout']) && (int) $options['timeout'] < 1000) {
            throw new ValidationException('timeout', 'must be >= 1000 milliseconds');
        }

        $async = $options['async'] ?? null;
        $storage = $options['storage'] ?? null;

        if ($async === true && $storage === null) {
            throw new ValidationException('storage', 'storage is required when async is true');
        }

        if (is_array($storage) && isset($storage['id'])) {
            $storageId = $storage['id'];
            if (!is_string($storageId) || trim($storageId) === '') {
                throw new ValidationException('storage.id', 'must be a non-empty string');
            }
        }

        if (isset($options['webhook'])) {
            $webhook = (string) $options['webhook'];
            if (!str_starts_with($webhook, 'https://')) {
                throw new ValidationException('webhook', 'must be an HTTPS URL');
            }
            if (filter_var($webhook, FILTER_VALIDATE_URL) === false) {
                throw new ValidationException('webhook', 'must be a valid URL');
            }
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function validateUrlOptions(array $options): void
    {
        $url = $options['url'] ?? null;
        $batch = $options['batch'] ?? null;
        $batchUrls = is_array($batch) ? ($batch['urls'] ?? null) : null;

        if ($url !== null && $batchUrls !== null) {
            throw new ValidationException('url', 'url and batch.urls are mutually exclusive');
        }

        if ($url === null && $batchUrls === null) {
            throw new ValidationException('url', 'either url or batch.urls is required');
        }

        if ($url !== null && filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new ValidationException('url', 'must be a valid URL');
        }

        if (is_array($batchUrls)) {
            if (count($batchUrls) > 20) {
                throw new ValidationException('batch.urls', 'maximum 20 URLs allowed');
            }
            foreach ($batchUrls as $i => $batchUrl) {
                if (filter_var($batchUrl, FILTER_VALIDATE_URL) === false) {
                    throw new ValidationException("batch.urls[{$i}]", 'must be a valid URL');
                }
            }
        }

        if (isset($options['authentication']) && is_array($options['authentication'])) {
            $auth = $options['authentication'];
            if (!isset($auth['username'], $auth['password'])) {
                throw new ValidationException('authentication', 'both username and password are required');
            }
        }

        if (isset($options['cookies']) && is_array($options['cookies'])) {
            foreach ($options['cookies'] as $i => $cookie) {
                if (is_array($cookie)) {
                    if (!isset($cookie['name']) || !isset($cookie['value'])) {
                        throw new ValidationException("cookies[{$i}]", 'name and value are required');
                    }
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function validateHtmlOptions(array $options): void
    {
        $html = $options['html'] ?? null;
        if (!is_string($html) || trim($html) === '') {
            throw new ValidationException('html', 'must be a non-empty string');
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function validateTemplateOptions(array $options): void
    {
        $templateId = $options['templateId'] ?? null;
        $template = $options['template'] ?? null;

        if ($templateId !== null && $template !== null) {
            throw new ValidationException('templateId', 'templateId and template are mutually exclusive');
        }

        if ($templateId === null && $template === null) {
            throw new ValidationException('templateId', 'either templateId or template is required');
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function validateImageOptions(array $options): void
    {
        if (isset($options['quality'])) {
            $quality = (int) $options['quality'];
            if ($quality < 0 || $quality > 100) {
                throw new ValidationException('quality', 'must be between 0 and 100');
            }
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function validateFileInput(array $options): void
    {
        if (!array_key_exists('file', $options) || $options['file'] === null) {
            throw new ValidationException('file', 'file is required');
        }
    }

    /**
     * @param StorageParams|array<string, mixed> $config
     */
    public static function validateStorageParams(StorageParams|array $config): void
    {
        if ($config instanceof StorageParams) {
            $config = $config->toArray();
        }

        $required = ['title', 'region', 'accessKeyId', 'secretAccessKey', 'bucket'];
        foreach ($required as $field) {
            if (!isset($config[$field]) || !is_string($config[$field]) || trim($config[$field]) === '') {
                throw new ValidationException($field, 'must be a non-empty string');
            }
        }

        if (isset($config['endpoint'])) {
            if (filter_var($config['endpoint'], FILTER_VALIDATE_URL) === false) {
                throw new ValidationException('endpoint', 'must be a valid URL');
            }
        }
    }

    private function __construct()
    {
    }
}
