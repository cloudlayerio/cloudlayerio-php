<?php

declare(strict_types=1);

namespace CloudLayer\Types;

/**
 * Asset data from the CloudLayer API.
 */
final class Asset
{
    public function __construct(
        public string $uid,
        public ?string $id = null,
        public string $fileId = '',
        public ?string $previewFileId = null,
        public ?string $type = null,
        public ?string $ext = null,
        public ?string $previewExt = null,
        public ?string $url = null,
        public ?string $previewUrl = null,
        public ?int $size = null,
        public ?int $timestamp = null,
        public ?string $projectId = null,
        public ?string $jobId = null,
        public ?string $name = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            uid: (string) ($data['uid'] ?? ''),
            id: isset($data['id']) ? (string) $data['id'] : null,
            fileId: (string) ($data['fileId'] ?? ''),
            previewFileId: isset($data['previewFileId']) ? (string) $data['previewFileId'] : null,
            type: isset($data['type']) ? (string) $data['type'] : null,
            ext: isset($data['ext']) ? (string) $data['ext'] : null,
            previewExt: isset($data['previewExt']) ? (string) $data['previewExt'] : null,
            url: isset($data['url']) ? (string) $data['url'] : null,
            previewUrl: isset($data['previewUrl']) ? (string) $data['previewUrl'] : null,
            size: isset($data['size']) ? (int) $data['size'] : null,
            timestamp: isset($data['timestamp']) ? (int) $data['timestamp'] : null,
            projectId: isset($data['projectId']) ? (string) $data['projectId'] : null,
            jobId: isset($data['jobId']) ? (string) $data['jobId'] : null,
            name: isset($data['name']) ? (string) $data['name'] : null,
        );
    }
}
