<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit\Api;

use CloudLayer\Tests\Unit\TestCase;
use CloudLayer\Types\Job;
use PHPUnit\Framework\Attributes\Test;

final class ConversionTest extends TestCase
{
    #[Test]
    public function urlToPdfSendsCorrectEndpoint(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('job.json')),
        ], $history);

        \CloudLayer\Api\Conversion::urlToPdf($http, \CloudLayer\Types\ApiVersion::V2, ['url' => 'https://example.com']);

        $request = $this->getLastRequest($history);
        self::assertStringContainsString('/url/pdf', $request->getUri()->getPath());
    }

    #[Test]
    public function htmlToPdfSendsCorrectEndpoint(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('job.json')),
        ], $history);

        \CloudLayer\Api\Conversion::htmlToPdf($http, \CloudLayer\Types\ApiVersion::V2, ['html' => base64_encode('<h1>Hi</h1>')]);

        $request = $this->getLastRequest($history);
        self::assertStringContainsString('/html/pdf', $request->getUri()->getPath());
    }

    #[Test]
    public function templateToPdfSendsCorrectEndpoint(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('job.json')),
        ], $history);

        \CloudLayer\Api\Conversion::templateToPdf($http, \CloudLayer\Types\ApiVersion::V2, ['templateId' => 'tmpl-1']);

        $request = $this->getLastRequest($history);
        self::assertStringContainsString('/template/pdf', $request->getUri()->getPath());
    }

    #[Test]
    public function v2ResponseReturnsJobInConversionResult(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('job.json')),
        ]);

        $result = \CloudLayer\Api\Conversion::urlToPdf($http, \CloudLayer\Types\ApiVersion::V2, ['url' => 'https://example.com']);

        self::assertInstanceOf(Job::class, $result->data);
        self::assertSame('job-123', $result->data->id);
    }

    #[Test]
    public function v1SyncResponseReturnsBinaryString(): void
    {
        $http = $this->createMockTransport([
            $this->mockBinaryResponse(200, '%PDF-1.4-binary-data'),
        ], apiVersion: 'v1');

        $result = \CloudLayer\Api\Conversion::urlToPdf($http, \CloudLayer\Types\ApiVersion::V1, ['url' => 'https://example.com']);

        self::assertIsString($result->data);
        self::assertStringStartsWith('%PDF', $result->data);
    }

    #[Test]
    public function conversionResponseHeadersPopulated(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('job.json'), [
                'cl-worker-job-id' => 'job-123',
                'cl-process-time' => '1500',
            ]),
        ]);

        $result = \CloudLayer\Api\Conversion::urlToPdf($http, \CloudLayer\Types\ApiVersion::V2, ['url' => 'https://example.com']);

        self::assertSame('job-123', $result->headers->workerJobId);
        self::assertSame(1500, $result->headers->processTime);
    }

    #[Test]
    public function postBodyContainsSerializedOptions(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('job.json')),
        ], $history);

        \CloudLayer\Api\Conversion::urlToPdf($http, \CloudLayer\Types\ApiVersion::V2, [
            'url' => 'https://example.com',
            'printBackground' => true,
        ]);

        $request = $this->getLastRequest($history);
        $body = json_decode((string) $request->getBody(), true);
        self::assertSame('https://example.com', $body['url']);
        self::assertTrue($body['printBackground']);
    }

    #[Test]
    public function allTenConversionMethodsExist(): void
    {
        $methods = [
            'urlToPdf', 'urlToImage', 'htmlToPdf', 'htmlToImage',
            'templateToPdf', 'templateToImage', 'docxToPdf', 'docxToHtml',
            'pdfToDocx', 'mergePdfs',
        ];

        foreach ($methods as $method) {
            self::assertTrue(
                method_exists(\CloudLayer\Api\Conversion::class, $method),
                "Method {$method} does not exist",
            );
        }
    }
}
