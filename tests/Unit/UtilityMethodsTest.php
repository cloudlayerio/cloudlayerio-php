<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit;

use CloudLayer\Errors\ApiException;
use CloudLayer\Errors\TimeoutException;
use CloudLayer\Errors\ValidationException;
use CloudLayer\Types\Job;
use CloudLayer\Types\JobStatus;
use PHPUnit\Framework\Attributes\Test;

final class UtilityMethodsTest extends TestCase
{
    #[Test]
    public function waitForJobPollsUntilSuccess(): void
    {
        $client = $this->createMockClient([
            $this->mockJsonResponse(200, $this->loadFixture('job-pending.json')),
            $this->mockJsonResponse(200, $this->loadFixture('job.json')),
        ]);

        $job = $client->waitForJob('job-123', [
            'interval' => 2000,
            'sleepFn' => static function (int $us): void {
            },
        ]);

        self::assertSame(JobStatus::Success, $job->status);
    }

    #[Test]
    public function waitForJobReturnsImmediatelyOnSuccess(): void
    {
        $client = $this->createMockClient([
            $this->mockJsonResponse(200, $this->loadFixture('job.json')),
        ]);

        $job = $client->waitForJob('job-123', [
            'sleepFn' => static function (int $us): void {
            },
        ]);

        self::assertSame(JobStatus::Success, $job->status);
    }

    #[Test]
    public function waitForJobThrowsOnError(): void
    {
        $client = $this->createMockClient([
            $this->mockJsonResponse(200, $this->loadFixture('job-error.json')),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Conversion failed');
        $client->waitForJob('job-123', [
            'sleepFn' => static function (int $us): void {
            },
        ]);
    }

    #[Test]
    public function waitForJobThrowsTimeoutException(): void
    {
        $pending = $this->loadFixture('job-pending.json');
        $client = $this->createMockClient([
            $this->mockJsonResponse(200, $pending),
            $this->mockJsonResponse(200, $pending),
            $this->mockJsonResponse(200, $pending),
        ]);

        $this->expectException(TimeoutException::class);
        $client->waitForJob('job-123', [
            'interval' => 2000,
            'maxWait' => 3000,
            'sleepFn' => static function (int $us): void {
            },
        ]);
    }

    #[Test]
    public function waitForJobIntervalTooLowThrows(): void
    {
        $client = $this->createMockClient([]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('interval');
        $client->waitForJob('job-123', ['interval' => 500]);
    }

    #[Test]
    public function waitForJobDefaultIntervalIs5000(): void
    {
        $sleepCalls = [];
        $client = $this->createMockClient([
            $this->mockJsonResponse(200, $this->loadFixture('job-pending.json')),
            $this->mockJsonResponse(200, $this->loadFixture('job.json')),
        ]);

        $client->waitForJob('job-123', [
            'sleepFn' => static function (int $us) use (&$sleepCalls): void {
                $sleepCalls[] = $us;
            },
        ]);

        self::assertCount(1, $sleepCalls);
        self::assertSame(5_000_000, $sleepCalls[0]); // 5000ms in microseconds
    }

    #[Test]
    public function downloadJobResultThrowsOnNullAssetUrl(): void
    {
        $client = $this->createMockClient([]);

        $job = new Job(id: 'job-1', uid: 'u', status: JobStatus::Pending, timestamp: 0);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('assetUrl');
        $client->downloadJobResult($job);
    }
}
