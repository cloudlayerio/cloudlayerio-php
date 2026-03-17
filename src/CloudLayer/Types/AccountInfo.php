<?php

declare(strict_types=1);

namespace CloudLayer\Types;

final readonly class AccountInfo
{
    /**
     * @param array<string, mixed>|null $extra Additional dynamic fields from Firestore
     */
    public function __construct(
        public string $email,
        public int $callsLimit,
        public int $calls,
        public int $storageUsed,
        public int $storageLimit,
        public string $subscription,
        public int $bytesTotal,
        public int $bytesLimit,
        public int $computeTimeTotal,
        public int $computeTimeLimit,
        public string $subType,
        public string $uid,
        public ?float $credit = null,
        public bool $subActive = false,
        public ?array $extra = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $knownKeys = [
            'email', 'callsLimit', 'calls', 'storageUsed', 'storageLimit',
            'subscription', 'bytesTotal', 'bytesLimit', 'computeTimeTotal',
            'computeTimeLimit', 'subType', 'uid', 'credit', 'subActive',
        ];

        $extra = array_diff_key($data, array_flip($knownKeys));

        return new self(
            email: (string) ($data['email'] ?? ''),
            callsLimit: (int) ($data['callsLimit'] ?? 0),
            calls: (int) ($data['calls'] ?? 0),
            storageUsed: (int) ($data['storageUsed'] ?? 0),
            storageLimit: (int) ($data['storageLimit'] ?? 0),
            subscription: (string) ($data['subscription'] ?? ''),
            bytesTotal: (int) ($data['bytesTotal'] ?? 0),
            bytesLimit: (int) ($data['bytesLimit'] ?? 0),
            computeTimeTotal: (int) ($data['computeTimeTotal'] ?? 0),
            computeTimeLimit: (int) ($data['computeTimeLimit'] ?? 0),
            subType: (string) ($data['subType'] ?? ''),
            uid: (string) ($data['uid'] ?? ''),
            credit: isset($data['credit']) ? (float) $data['credit'] : null,
            subActive: (bool) ($data['subActive'] ?? false),
            extra: $extra !== [] ? $extra : null,
        );
    }
}
