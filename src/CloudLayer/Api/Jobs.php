<?php

declare(strict_types=1);

namespace CloudLayer\Api;

use CloudLayer\Errors\ValidationException;
use CloudLayer\HttpTransport;
use CloudLayer\Types\Job;

/**
 * Jobs API methods.
 */
final class Jobs
{
    /**
     * List all jobs for the account.
     *
     * WARNING: Returns ALL jobs -- no server-side pagination. Each call reads
     * every Firestore job document. For single-job lookups, use getJob() instead.
     *
     * @return list<Job>
     */
    public static function listJobs(HttpTransport $http): array
    {
        $response = $http->get('/jobs', ['retryable' => true]);

        if (!is_array($response->data)) {
            return [];
        }

        return array_map(
            static fn (array $item): Job => Job::fromArray($item),
            array_values($response->data),
        );
    }

    public static function getJob(HttpTransport $http, string $jobId): Job
    {
        if (trim($jobId) === '') {
            throw new ValidationException('jobId', 'must be a non-empty string');
        }

        $response = $http->get("/jobs/{$jobId}", ['retryable' => true]);

        /** @var array<string, mixed> $data */
        $data = $response->data;

        return Job::fromArray($data);
    }

    private function __construct()
    {
    }
}
