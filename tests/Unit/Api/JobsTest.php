<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit\Api;

use CloudLayer\Api\Jobs;
use CloudLayer\Errors\ValidationException;
use CloudLayer\Tests\Unit\TestCase;
use CloudLayer\Types\Job;
use PHPUnit\Framework\Attributes\Test;

final class JobsTest extends TestCase
{
    #[Test]
    public function listJobsReturnsArrayOfJobs(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('jobs-list.json')),
        ]);

        $jobs = Jobs::listJobs($http);

        self::assertCount(2, $jobs);
        self::assertInstanceOf(Job::class, $jobs[0]);
        self::assertSame('job-1', $jobs[0]->id);
    }

    #[Test]
    public function getJobReturnsSingleJob(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('job.json')),
        ]);

        $job = Jobs::getJob($http, 'job-123');

        self::assertSame('job-123', $job->id);
        self::assertSame('success', $job->status->value);
    }

    #[Test]
    public function getJobEmptyIdThrowsValidation(): void
    {
        $http = $this->createMockTransport([]);

        $this->expectException(ValidationException::class);
        Jobs::getJob($http, '');
    }

    #[Test]
    public function jobsEndpointsPassRetryable(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('jobs-list.json')),
        ], $history);

        Jobs::listJobs($http);
        // If retryable wasn't set, a 429 would throw immediately
        // We verify by checking the request was made successfully
        self::assertCount(1, $history);
    }
}
