<?php

declare(strict_types=1);

namespace CloudLayer\Types;

/**
 * Job data from the CloudLayer API.
 */
final class Job
{
    /**
     * @param ?array<string, mixed> $params
     */
    public function __construct(
        public string $id,
        public string $uid,
        public JobStatus $status,
        public int $timestamp,
        public ?string $name = null,
        public ?string $type = null,
        public ?string $workerName = null,
        public ?int $processTime = null,
        public ?string $apiKeyUsed = null,
        public ?float $processTimeCost = null,
        public ?float $apiCreditCost = null,
        public ?float $bandwidthCost = null,
        public ?float $totalCost = null,
        public ?int $size = null,
        public ?array $params = null,
        public ?string $assetUrl = null,
        public ?string $previewUrl = null,
        public ?string $self = null,
        public ?string $assetId = null,
        public ?string $projectId = null,
        public ?string $error = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            uid: (string) ($data['uid'] ?? ''),
            status: JobStatus::tryFrom((string) ($data['status'] ?? '')) ?? JobStatus::Pending,
            timestamp: (int) ($data['timestamp'] ?? 0),
            name: isset($data['name']) ? (string) $data['name'] : null,
            type: isset($data['type']) ? (string) $data['type'] : null,
            workerName: isset($data['workerName']) ? (string) $data['workerName'] : null,
            processTime: isset($data['processTime']) ? (int) $data['processTime'] : null,
            apiKeyUsed: isset($data['apiKeyUsed']) ? (string) $data['apiKeyUsed'] : null,
            processTimeCost: isset($data['processTimeCost']) ? (float) $data['processTimeCost'] : null,
            apiCreditCost: isset($data['apiCreditCost']) ? (float) $data['apiCreditCost'] : null,
            bandwidthCost: isset($data['bandwidthCost']) ? (float) $data['bandwidthCost'] : null,
            totalCost: isset($data['totalCost']) ? (float) $data['totalCost'] : null,
            size: isset($data['size']) ? (int) $data['size'] : null,
            params: isset($data['params']) && is_array($data['params']) ? $data['params'] : null,
            assetUrl: isset($data['assetUrl']) ? (string) $data['assetUrl'] : null,
            previewUrl: isset($data['previewUrl']) ? (string) $data['previewUrl'] : null,
            self: isset($data['self']) ? (string) $data['self'] : null,
            assetId: isset($data['assetId']) ? (string) $data['assetId'] : null,
            projectId: isset($data['projectId']) ? (string) $data['projectId'] : null,
            error: isset($data['error']) ? (string) $data['error'] : null,
        );
    }
}
