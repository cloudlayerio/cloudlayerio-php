<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit\Types;

use CloudLayer\Tests\Unit\TestCase;
use CloudLayer\Types\ConversionResult;
use CloudLayer\Types\Job;
use CloudLayer\Types\ResponseHeaders;
use PHPUnit\Framework\Attributes\Test;

final class ConversionResultTest extends TestCase
{
    #[Test]
    public function constructionWithJob(): void
    {
        $job = Job::fromArray($this->loadFixture('job.json'));
        $headers = new ResponseHeaders(workerJobId: 'job-123');

        $result = new ConversionResult($job, $headers, 200);

        self::assertInstanceOf(Job::class, $result->data);
        self::assertSame(200, $result->status);
    }

    #[Test]
    public function constructionWithBinaryString(): void
    {
        $headers = new ResponseHeaders();

        $result = new ConversionResult('%PDF-data', $headers, 200, 'report.pdf');

        self::assertSame('%PDF-data', $result->data);
        self::assertSame('report.pdf', $result->filename);
    }

    #[Test]
    public function responseHeadersAllPresent(): void
    {
        $headers = ResponseHeaders::fromHeaders([
            'cl-worker-job-id' => ['job-1'],
            'cl-cluster-id' => ['cluster-1'],
            'cl-worker' => ['worker-1'],
            'cl-bandwidth' => ['1024'],
            'cl-process-time' => ['1500'],
            'cl-calls-remaining' => ['958'],
            'cl-charged-time' => ['2000'],
            'cl-bandwidth-cost' => ['0.001'],
            'cl-process-time-cost' => ['0.0015'],
            'cl-api-credit-cost' => ['1.5'],
        ]);

        self::assertSame('job-1', $headers->workerJobId);
        self::assertSame(1024, $headers->bandwidth);
        self::assertSame(0.001, $headers->bandwidthCost);
        self::assertSame(1.5, $headers->apiCreditCost);
    }

    #[Test]
    public function responseHeadersAllNull(): void
    {
        $headers = ResponseHeaders::fromHeaders([]);

        self::assertNull($headers->workerJobId);
        self::assertNull($headers->bandwidth);
        self::assertNull($headers->bandwidthCost);
    }

    #[Test]
    public function responseHeadersNonNumericDefaultsNull(): void
    {
        $headers = ResponseHeaders::fromHeaders([
            'cl-bandwidth' => ['not-a-number'],
        ]);

        self::assertNull($headers->bandwidth);
    }
}
