<?php

declare(strict_types=1);

namespace CloudLayer;

use CloudLayer\Api\Account;
use CloudLayer\Api\Assets;
use CloudLayer\Api\Conversion;
use CloudLayer\Api\Jobs;
use CloudLayer\Api\Storage;
use CloudLayer\Api\Templates;
use CloudLayer\Errors\ApiException;
use CloudLayer\Errors\ConfigException;
use CloudLayer\Errors\TimeoutException;
use CloudLayer\Errors\ValidationException;
use CloudLayer\Types\AccountInfo;
use CloudLayer\Types\ApiVersion;
use CloudLayer\Types\Asset;
use CloudLayer\Types\Config;
use CloudLayer\Types\ConversionResult;
use CloudLayer\Types\Job;
use CloudLayer\Types\PublicTemplate;
use CloudLayer\Types\StatusResponse;
use CloudLayer\Types\StorageDetail;
use CloudLayer\Types\StorageListItem;
use CloudLayer\Types\StorageNotAllowedResponse;
use CloudLayer\Types\StorageParams;
use GuzzleHttp\HandlerStack;

/**
 * CloudLayer.io SDK client.
 *
 * Main entry point for interacting with the CloudLayer.io document generation API.
 *
 * @see https://cloudlayer.io/docs
 */
final class CloudLayer
{
    public readonly ApiVersion $apiVersion;
    public readonly string $baseUrl;

    /** @internal */
    public readonly HttpTransport $http;

    private readonly Config $config;

    /** @var callable(int): void */
    private $waitSleepFn;

    /**
     * @param string $apiKey Your CloudLayer.io API key
     * @param ApiVersion|string $apiVersion API version ('v1' or 'v2')
     * @param string $baseUrl API base URL
     * @param int $timeout Request timeout in milliseconds
     * @param int $maxRetries Max retries for retryable requests (0-5)
     * @param array<string, string> $headers Additional HTTP headers
     * @param ?HandlerStack $handler Guzzle handler stack (for testing)
     * @param ?callable(int): void $sleepFn Sleep function for transport (for testing)
     *
     * @throws ConfigException If configuration is invalid
     */
    public function __construct(
        string $apiKey,
        ApiVersion|string $apiVersion,
        string $baseUrl = 'https://api.cloudlayer.io',
        int $timeout = 30000,
        int $maxRetries = 2,
        array $headers = [],
        ?HandlerStack $handler = null,
        ?callable $sleepFn = null,
    ) {
        $version = $this->resolveApiVersion($apiVersion);

        $this->config = new Config(
            apiKey: $apiKey,
            apiVersion: $version,
            baseUrl: $baseUrl,
            timeout: $timeout,
            maxRetries: $maxRetries,
            headers: $headers,
        );

        $this->apiVersion = $this->config->apiVersion;
        $this->baseUrl = $this->config->baseUrl;
        $this->waitSleepFn = $sleepFn ?? usleep(...);

        $this->http = new HttpTransport(
            apiKey: $this->config->apiKey,
            baseUrl: $this->config->baseUrl,
            apiVersion: $this->config->apiVersion,
            timeoutMs: $this->config->timeout,
            maxRetries: $this->config->maxRetries,
            headers: $this->config->headers,
            handler: $handler,
            sleepFn: $sleepFn,
        );
    }

    // ─── Conversion Methods ─────────────────────────────────────────────

    /**
     * @param array<string, mixed> $options
     */
    public function urlToPdf(array $options): ConversionResult
    {
        return Conversion::urlToPdf($this->http, $this->apiVersion, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function urlToImage(array $options): ConversionResult
    {
        return Conversion::urlToImage($this->http, $this->apiVersion, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function htmlToPdf(array $options): ConversionResult
    {
        return Conversion::htmlToPdf($this->http, $this->apiVersion, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function htmlToImage(array $options): ConversionResult
    {
        return Conversion::htmlToImage($this->http, $this->apiVersion, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function templateToPdf(array $options): ConversionResult
    {
        return Conversion::templateToPdf($this->http, $this->apiVersion, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function templateToImage(array $options): ConversionResult
    {
        return Conversion::templateToImage($this->http, $this->apiVersion, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function docxToPdf(array $options): ConversionResult
    {
        return Conversion::docxToPdf($this->http, $this->apiVersion, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function docxToHtml(array $options): ConversionResult
    {
        return Conversion::docxToHtml($this->http, $this->apiVersion, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function pdfToDocx(array $options): ConversionResult
    {
        return Conversion::pdfToDocx($this->http, $this->apiVersion, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function mergePdfs(array $options): ConversionResult
    {
        return Conversion::mergePdfs($this->http, $this->apiVersion, $options);
    }

    // ─── Jobs ────────────────────────────────────────────────────────────

    /**
     * List all jobs.
     *
     * WARNING: Returns ALL jobs -- no server-side pagination.
     *
     * @return list<Job>
     */
    public function listJobs(): array
    {
        return Jobs::listJobs($this->http);
    }

    public function getJob(string $jobId): Job
    {
        return Jobs::getJob($this->http, $jobId);
    }

    // ─── Assets ──────────────────────────────────────────────────────────

    /**
     * List all assets.
     *
     * WARNING: Returns ALL assets -- no server-side pagination.
     *
     * @return list<Asset>
     */
    public function listAssets(): array
    {
        return Assets::listAssets($this->http);
    }

    public function getAsset(string $assetId): Asset
    {
        return Assets::getAsset($this->http, $assetId);
    }

    // ─── Storage ─────────────────────────────────────────────────────────

    /**
     * @return list<StorageListItem>
     */
    public function listStorage(): array
    {
        return Storage::listStorage($this->http);
    }

    public function getStorage(string $storageId): StorageDetail
    {
        return Storage::getStorage($this->http, $storageId);
    }

    /**
     * @param StorageParams|array<string, mixed> $config
     */
    public function addStorage(StorageParams|array $config): StorageDetail|StorageNotAllowedResponse
    {
        return Storage::addStorage($this->http, $config);
    }

    public function deleteStorage(string $storageId): void
    {
        Storage::deleteStorage($this->http, $storageId);
    }

    // ─── Account ─────────────────────────────────────────────────────────

    public function getAccount(): AccountInfo
    {
        return Account::getAccount($this->http);
    }

    public function getStatus(): StatusResponse
    {
        return Account::getStatus($this->http);
    }

    // ─── Templates ───────────────────────────────────────────────────────

    /**
     * @param ?array<string, mixed> $options
     * @return list<PublicTemplate>
     */
    public function listTemplates(?array $options = null): array
    {
        return Templates::listTemplates($this->http, $options);
    }

    public function getTemplate(string $templateId): PublicTemplate
    {
        return Templates::getTemplate($this->http, $templateId);
    }

    // ─── Utility Methods ─────────────────────────────────────────────────

    /**
     * Download the binary result from a completed job.
     *
     * Essential for v2 users — v2 conversion responses return a Job object,
     * not binary data. Use this method to fetch the actual file.
     */
    public function downloadJobResult(Job $job): string
    {
        if ($job->assetUrl === null) {
            throw new ValidationException(
                'assetUrl',
                'job has no assetUrl (may not be complete or may have failed)',
            );
        }

        return Conversion::downloadJobResult($job->assetUrl);
    }

    /**
     * Poll getJob() until the job reaches a terminal status.
     *
     * @param array{interval?: int, maxWait?: int, sleepFn?: callable(int): void} $options
     *
     * @throws ValidationException If interval < 2000ms
     * @throws TimeoutException If maxWait exceeded
     * @throws ApiException If job status is 'error'
     */
    public function waitForJob(string $jobId, array $options = []): Job
    {
        $interval = (int) ($options['interval'] ?? 5000);
        $maxWait = (int) ($options['maxWait'] ?? 300_000);
        $sleepFn = $options['sleepFn'] ?? $this->waitSleepFn;

        if ($interval < 2000) {
            throw new ValidationException('interval', 'must be >= 2000 milliseconds');
        }

        $elapsed = 0;

        while (true) {
            $job = $this->getJob($jobId);

            if ($job->status === \CloudLayer\Types\JobStatus::Success) {
                return $job;
            }

            if ($job->status === \CloudLayer\Types\JobStatus::Error) {
                throw new ApiException(
                    'Job failed: ' . ($job->error ?? 'unknown error'),
                    0,
                    'Job Error',
                    ['error' => $job->error],
                    "/jobs/{$jobId}",
                    'GET',
                );
            }

            // Preemptive timeout check
            if ($elapsed + $interval > $maxWait) {
                throw new TimeoutException(
                    $maxWait,
                    "/jobs/{$jobId}",
                    'GET',
                );
            }

            $sleepFn($interval * 1000); // convert ms to microseconds
            $elapsed += $interval;
        }
    }

    private function resolveApiVersion(ApiVersion|string $version): ApiVersion
    {
        if ($version instanceof ApiVersion) {
            return $version;
        }

        $resolved = ApiVersion::tryFrom($version);
        if ($resolved === null) {
            throw new ConfigException('apiVersion is required and must be "v1" or "v2"');
        }

        return $resolved;
    }
}
