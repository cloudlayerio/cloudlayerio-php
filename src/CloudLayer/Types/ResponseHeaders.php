<?php

declare(strict_types=1);

namespace CloudLayer\Types;

/**
 * Parsed CloudLayer custom response headers (cl-* headers).
 */
final readonly class ResponseHeaders
{
    public function __construct(
        public ?string $workerJobId = null,
        public ?string $clusterId = null,
        public ?string $worker = null,
        public ?int $bandwidth = null,
        public ?int $processTime = null,
        public ?int $callsRemaining = null,
        public ?int $chargedTime = null,
        public ?float $bandwidthCost = null,
        public ?float $processTimeCost = null,
        public ?float $apiCreditCost = null,
    ) {
    }

    /**
     * Parse cl-* headers from a raw headers array.
     *
     * @param array<string, list<string>|string> $headers
     */
    public static function fromHeaders(array $headers): self
    {
        return new self(
            workerJobId: self::getString($headers, 'cl-worker-job-id'),
            clusterId: self::getString($headers, 'cl-cluster-id'),
            worker: self::getString($headers, 'cl-worker'),
            bandwidth: self::getInt($headers, 'cl-bandwidth'),
            processTime: self::getInt($headers, 'cl-process-time'),
            callsRemaining: self::getInt($headers, 'cl-calls-remaining'),
            chargedTime: self::getInt($headers, 'cl-charged-time'),
            bandwidthCost: self::getFloat($headers, 'cl-bandwidth-cost'),
            processTimeCost: self::getFloat($headers, 'cl-process-time-cost'),
            apiCreditCost: self::getFloat($headers, 'cl-api-credit-cost'),
        );
    }

    /**
     * @param array<string, list<string>|string> $headers
     */
    private static function getHeaderValue(array $headers, string $name): ?string
    {
        // Case-insensitive header lookup
        foreach ($headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return is_array($value) ? ($value[0] ?? null) : $value;
            }
        }

        return null;
    }

    /**
     * @param array<string, list<string>|string> $headers
     */
    private static function getString(array $headers, string $name): ?string
    {
        return self::getHeaderValue($headers, $name);
    }

    /**
     * @param array<string, list<string>|string> $headers
     */
    private static function getInt(array $headers, string $name): ?int
    {
        $value = self::getHeaderValue($headers, $name);
        if ($value === null) {
            return null;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_INT);

        return $parsed !== false ? $parsed : null;
    }

    /**
     * @param array<string, list<string>|string> $headers
     */
    private static function getFloat(array $headers, string $name): ?float
    {
        $value = self::getHeaderValue($headers, $name);
        if ($value === null) {
            return null;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_FLOAT);

        return $parsed !== false ? $parsed : null;
    }
}
