<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit;

use CloudLayer\Errors\ValidationException;
use CloudLayer\Types\StorageParams;
use CloudLayer\Utils\Validator;
use PHPUnit\Framework\Attributes\Test;

final class ValidatorTest extends TestCase
{
    // ─── Base Options ────────────────────────────────────────────────────

    #[Test]
    public function baseOptionsValidPass(): void
    {
        Validator::validateBaseOptions(['timeout' => 5000, 'webhook' => 'https://example.com/hook']);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function baseOptionsTimeoutTooLow(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validateBaseOptions(['timeout' => 500]);
    }

    #[Test]
    public function baseOptionsAsyncWithoutStorage(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('storage');
        Validator::validateBaseOptions(['async' => true]);
    }

    #[Test]
    public function baseOptionsStorageIdEmpty(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validateBaseOptions(['storage' => ['id' => '']]);
    }

    #[Test]
    public function baseOptionsWebhookNonHttps(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('HTTPS');
        Validator::validateBaseOptions(['webhook' => 'http://example.com/hook']);
    }

    // ─── URL Options ─────────────────────────────────────────────────────

    #[Test]
    public function urlOptionsValidUrl(): void
    {
        Validator::validateUrlOptions(['url' => 'https://example.com']);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function urlOptionsInvalidUrl(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validateUrlOptions(['url' => 'not-a-url']);
    }

    #[Test]
    public function urlOptionsMutualExclusion(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('mutually exclusive');
        Validator::validateUrlOptions(['url' => 'https://a.com', 'batch' => ['urls' => ['https://b.com']]]);
    }

    #[Test]
    public function urlOptionsBatchTooMany(): void
    {
        $urls = array_fill(0, 21, 'https://example.com');
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('maximum 20');
        Validator::validateUrlOptions(['batch' => ['urls' => $urls]]);
    }

    #[Test]
    public function urlOptionsNeitherUrlNorBatch(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validateUrlOptions([]);
    }

    #[Test]
    public function urlOptionsAuthMissingPassword(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('authentication');
        Validator::validateUrlOptions(['url' => 'https://a.com', 'authentication' => ['username' => 'user']]);
    }

    #[Test]
    public function urlOptionsCookieMissingName(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validateUrlOptions(['url' => 'https://a.com', 'cookies' => [['value' => 'v']]]);
    }

    // ─── HTML Options ────────────────────────────────────────────────────

    #[Test]
    public function htmlOptionsEmptyFails(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validateHtmlOptions(['html' => '']);
    }

    // ─── Template Options ────────────────────────────────────────────────

    #[Test]
    public function templateOptionsBothFails(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('mutually exclusive');
        Validator::validateTemplateOptions(['templateId' => 'id', 'template' => 'tmpl']);
    }

    #[Test]
    public function templateOptionsNeitherFails(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validateTemplateOptions([]);
    }

    // ─── Image Options ───────────────────────────────────────────────────

    #[Test]
    public function imageOptionsQualityTooHigh(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validateImageOptions(['quality' => 101]);
    }

    #[Test]
    public function imageOptionsQualityNegative(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validateImageOptions(['quality' => -1]);
    }

    // ─── File Input ──────────────────────────────────────────────────────

    #[Test]
    public function fileInputMissing(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validateFileInput([]);
    }

    // ─── Storage Params ──────────────────────────────────────────────────

    #[Test]
    public function storageParamsValidPass(): void
    {
        Validator::validateStorageParams(new StorageParams('title', 'us-east-1', 'key', 'secret', 'bucket'));
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function storageParamsEmptyTitle(): void
    {
        $this->expectException(ValidationException::class);
        Validator::validateStorageParams(['title' => '', 'region' => 'r', 'accessKeyId' => 'k', 'secretAccessKey' => 's', 'bucket' => 'b']);
    }

    #[Test]
    public function storageParamsInvalidEndpoint(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('endpoint');
        Validator::validateStorageParams(['title' => 't', 'region' => 'r', 'accessKeyId' => 'k', 'secretAccessKey' => 's', 'bucket' => 'b', 'endpoint' => 'not-url']);
    }

    #[Test]
    public function storageParamsValidEndpoint(): void
    {
        Validator::validateStorageParams([
            'title' => 't', 'region' => 'r', 'accessKeyId' => 'k',
            'secretAccessKey' => 's', 'bucket' => 'b', 'endpoint' => 'https://s3.example.com',
        ]);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function storageParamsNullEndpoint(): void
    {
        Validator::validateStorageParams(new StorageParams('t', 'r', 'k', 's', 'b', null));
        $this->addToAssertionCount(1);
    }
}
