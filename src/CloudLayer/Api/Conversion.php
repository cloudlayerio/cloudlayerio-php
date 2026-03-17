<?php

declare(strict_types=1);

namespace CloudLayer\Api;

use CloudLayer\HttpResponse;
use CloudLayer\HttpTransport;
use CloudLayer\Types\ApiVersion;
use CloudLayer\Types\ConversionResult;
use CloudLayer\Types\Job;
use CloudLayer\Utils\OptionSerializer;
use CloudLayer\Utils\Validator;

/**
 * Conversion API methods (URL/HTML/Template/Document conversions).
 */
final class Conversion
{
    /**
     * @param array<string, mixed> $options
     */
    public static function urlToPdf(HttpTransport $http, ApiVersion $apiVersion, array $options): ConversionResult
    {
        Validator::validateBaseOptions($options);
        Validator::validateUrlOptions($options);

        return self::makeConversionRequest($http, $apiVersion, '/url/pdf', $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function urlToImage(HttpTransport $http, ApiVersion $apiVersion, array $options): ConversionResult
    {
        Validator::validateBaseOptions($options);
        Validator::validateUrlOptions($options);
        Validator::validateImageOptions($options);

        return self::makeConversionRequest($http, $apiVersion, '/url/image', $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function htmlToPdf(HttpTransport $http, ApiVersion $apiVersion, array $options): ConversionResult
    {
        Validator::validateBaseOptions($options);
        Validator::validateHtmlOptions($options);

        return self::makeConversionRequest($http, $apiVersion, '/html/pdf', $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function htmlToImage(HttpTransport $http, ApiVersion $apiVersion, array $options): ConversionResult
    {
        Validator::validateBaseOptions($options);
        Validator::validateHtmlOptions($options);
        Validator::validateImageOptions($options);

        return self::makeConversionRequest($http, $apiVersion, '/html/image', $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function templateToPdf(HttpTransport $http, ApiVersion $apiVersion, array $options): ConversionResult
    {
        Validator::validateBaseOptions($options);
        Validator::validateTemplateOptions($options);

        return self::makeConversionRequest($http, $apiVersion, '/template/pdf', $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function templateToImage(HttpTransport $http, ApiVersion $apiVersion, array $options): ConversionResult
    {
        Validator::validateBaseOptions($options);
        Validator::validateTemplateOptions($options);
        Validator::validateImageOptions($options);

        return self::makeConversionRequest($http, $apiVersion, '/template/image', $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function docxToPdf(HttpTransport $http, ApiVersion $apiVersion, array $options): ConversionResult
    {
        Validator::validateBaseOptions($options);
        Validator::validateFileInput($options);

        return self::makeMultipartConversionRequest($http, $apiVersion, '/docx/pdf', $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function docxToHtml(HttpTransport $http, ApiVersion $apiVersion, array $options): ConversionResult
    {
        Validator::validateBaseOptions($options);
        Validator::validateFileInput($options);

        return self::makeMultipartConversionRequest($http, $apiVersion, '/docx/html', $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function pdfToDocx(HttpTransport $http, ApiVersion $apiVersion, array $options): ConversionResult
    {
        Validator::validateBaseOptions($options);
        Validator::validateFileInput($options);

        return self::makeMultipartConversionRequest($http, $apiVersion, '/pdf/docx', $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function mergePdfs(HttpTransport $http, ApiVersion $apiVersion, array $options): ConversionResult
    {
        Validator::validateBaseOptions($options);
        Validator::validateUrlOptions($options);

        return self::makeConversionRequest($http, $apiVersion, '/pdf/merge', $options);
    }

    /**
     * Download the binary result from a job's asset URL.
     */
    public static function downloadJobResult(string $assetUrl): string
    {
        $client = new \GuzzleHttp\Client([
            'http_errors' => false,
            'timeout' => 60,
        ]);

        $response = $client->get($assetUrl);
        $status = $response->getStatusCode();

        if ($status === 403) {
            throw new \CloudLayer\Errors\ApiException(
                'Asset URL has expired or is inaccessible (403)',
                403,
                $response->getReasonPhrase(),
                null,
                $assetUrl,
                'GET',
            );
        }

        if ($status >= 400) {
            throw new \CloudLayer\Errors\ApiException(
                "Failed to download asset: {$status}",
                $status,
                $response->getReasonPhrase(),
                null,
                $assetUrl,
                'GET',
            );
        }

        return (string) $response->getBody();
    }

    /**
     * @param array<string, mixed> $options
     */
    private static function makeConversionRequest(
        HttpTransport $http,
        ApiVersion $apiVersion,
        string $endpoint,
        array $options,
    ): ConversionResult {
        $serialized = OptionSerializer::serialize($options);

        $response = $http->post($endpoint, $serialized);

        return self::buildConversionResult($response, $apiVersion);
    }

    /**
     * @param array<string, mixed> $options
     */
    private static function makeMultipartConversionRequest(
        HttpTransport $http,
        ApiVersion $apiVersion,
        string $endpoint,
        array $options,
    ): ConversionResult {
        $file = $options['file'];
        unset($options['file']);

        $multipart = self::buildMultipartData($file, $options);

        $response = $http->postMultipart($endpoint, $multipart);

        return self::buildConversionResult($response, $apiVersion);
    }

    private static function buildConversionResult(HttpResponse $response, ApiVersion $apiVersion): ConversionResult
    {
        $data = $response->data;

        // v2 always returns JSON Job; v1 sync returns binary
        if ($apiVersion === ApiVersion::V2 && is_array($data)) {
            $data = Job::fromArray($data);
        } elseif (is_array($data) && isset($data['id'], $data['status'])) {
            // v1 async also returns a Job
            $data = Job::fromArray($data);
        }

        if (!is_string($data) && !$data instanceof Job) {
            $data = (string) json_encode($data);
        }

        return new ConversionResult(
            data: $data,
            headers: $response->cloudlayerHeaders,
            status: $response->status,
            filename: $response->filename,
        );
    }

    /**
     * @param string|resource $file
     * @param array<string, mixed> $options
     * @return list<array{name: string, contents: string|resource, filename?: string}>
     */
    private static function buildMultipartData(mixed $file, array $options): array
    {
        $multipart = [];

        // Build file part
        if (is_resource($file)) {
            $multipart[] = [
                'name' => 'file',
                'contents' => $file,
                'filename' => 'file',
            ];
        } elseif (is_string($file) && file_exists($file)) {
            $stream = fopen($file, 'r');
            if ($stream === false) {
                throw new \CloudLayer\Errors\ValidationException('file', "cannot read file: {$file}");
            }
            $multipart[] = [
                'name' => 'file',
                'contents' => $stream,
                'filename' => basename($file),
            ];
        } elseif (is_string($file)) {
            $multipart[] = [
                'name' => 'file',
                'contents' => $file,
                'filename' => 'file',
            ];
        }

        // Add other options as form fields
        $serialized = OptionSerializer::serialize($options);
        foreach ($serialized as $key => $value) {
            $multipart[] = [
                'name' => $key,
                'contents' => is_string($value) || is_numeric($value)
                    ? (string) $value
                    : (string) json_encode($value),
            ];
        }

        return $multipart;
    }

    private function __construct()
    {
    }
}
