<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit\Types;

use CloudLayer\Tests\Unit\TestCase;
use CloudLayer\Types\Job;
use CloudLayer\Types\JobStatus;
use PHPUnit\Framework\Attributes\Test;

final class JobTest extends TestCase
{
    #[Test]
    public function fromArrayWithFullData(): void
    {
        $data = $this->loadFixture('job.json');
        $job = Job::fromArray($data);

        self::assertSame('job-123', $job->id);
        self::assertSame('user-456', $job->uid);
        self::assertSame(JobStatus::Success, $job->status);
        self::assertSame(1710000000, $job->timestamp);
        self::assertSame('test-job', $job->name);
        self::assertSame('url-pdf', $job->type);
        self::assertSame(102400, $job->size);
        self::assertSame('https://storage.example.com/asset.pdf', $job->assetUrl);
    }

    #[Test]
    public function fromArrayWithMinimalData(): void
    {
        $job = Job::fromArray(['id' => 'j1', 'uid' => 'u1', 'status' => 'pending', 'timestamp' => 0]);

        self::assertSame('j1', $job->id);
        self::assertNull($job->name);
        self::assertNull($job->assetUrl);
        self::assertNull($job->error);
    }

    #[Test]
    public function fromArrayWithUnknownFields(): void
    {
        $job = Job::fromArray(['id' => 'j1', 'uid' => 'u1', 'status' => 'success', 'timestamp' => 0, 'unknownField' => 'value']);
        self::assertSame('j1', $job->id);
    }

    #[Test]
    public function fromArrayWithEmptyArray(): void
    {
        $job = Job::fromArray([]);
        self::assertSame('', $job->id);
        self::assertSame(JobStatus::Pending, $job->status);
    }

    #[Test]
    public function jobStatusEnumValues(): void
    {
        self::assertSame('pending', JobStatus::Pending->value);
        self::assertSame('success', JobStatus::Success->value);
        self::assertSame('error', JobStatus::Error->value);
    }
}
