<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Smoke;

use CloudLayer\CloudLayer;
use CloudLayer\Types\JobStatus;
use CloudLayer\Types\PublicTemplate;
use CloudLayer\Types\StatusResponse;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Live API smoke tests. Require CLOUDLAYER_API_KEY environment variable.
 *
 * Run with: CLOUDLAYER_API_KEY=xxx vendor/bin/phpunit --group smoke
 */
#[Group('smoke')]
final class LiveApiTest extends TestCase
{
    private CloudLayer $client;

    protected function setUp(): void
    {
        $apiKey = getenv('CLOUDLAYER_API_KEY');
        if ($apiKey === false || $apiKey === '') {
            self::markTestSkipped('CLOUDLAYER_API_KEY not set');
        }

        $this->client = new CloudLayer(apiKey: $apiKey, apiVersion: 'v2');
    }

    #[Test]
    public function getStatusReturnsOk(): void
    {
        $status = $this->client->getStatus();
        self::assertInstanceOf(StatusResponse::class, $status);
        self::assertStringContainsString('ok', $status->status);
    }

    #[Test]
    public function getAccountReturnsInfo(): void
    {
        $account = $this->client->getAccount();
        self::assertNotEmpty($account->email);
        self::assertNotEmpty($account->uid);
    }

    #[Test]
    public function listTemplatesReturnsTemplates(): void
    {
        $templates = $this->client->listTemplates();
        self::assertNotEmpty($templates);
        self::assertInstanceOf(PublicTemplate::class, $templates[0]);
    }

    #[Test]
    public function urlToPdfGeneratesPdf(): void
    {
        $result = $this->client->urlToPdf(['url' => 'https://example.com']);

        self::assertInstanceOf(\CloudLayer\Types\Job::class, $result->data);

        $job = $this->client->waitForJob($result->data->id, ['interval' => 3000, 'maxWait' => 60000]);
        self::assertSame(JobStatus::Success, $job->status);

        $binary = $this->client->downloadJobResult($job);
        self::assertStringStartsWith('%PDF', $binary);
    }

    #[Test]
    public function htmlToImageGeneratesImage(): void
    {
        $html = base64_encode('<html><body><h1>Test</h1></body></html>');
        $result = $this->client->htmlToImage(['html' => $html, 'imageType' => 'png']);

        self::assertInstanceOf(\CloudLayer\Types\Job::class, $result->data);

        $job = $this->client->waitForJob($result->data->id, ['interval' => 3000, 'maxWait' => 60000]);
        self::assertSame(JobStatus::Success, $job->status);
    }
}
