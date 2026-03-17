<?php

declare(strict_types=1);

namespace CloudLayer\Types;

final readonly class StorageParams
{
    public function __construct(
        public string $title,
        public string $region,
        public string $accessKeyId,
        public string $secretAccessKey,
        public string $bucket,
        public ?string $endpoint = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [
            'title' => $this->title,
            'region' => $this->region,
            'accessKeyId' => $this->accessKeyId,
            'secretAccessKey' => $this->secretAccessKey,
            'bucket' => $this->bucket,
        ];

        if ($this->endpoint !== null) {
            $result['endpoint'] = $this->endpoint;
        }

        return $result;
    }
}
