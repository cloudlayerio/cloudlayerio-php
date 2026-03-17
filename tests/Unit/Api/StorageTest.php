<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit\Api;

use CloudLayer\Api\Storage;
use CloudLayer\Errors\ValidationException;
use CloudLayer\Tests\Unit\TestCase;
use CloudLayer\Types\StorageDetail;
use CloudLayer\Types\StorageListItem;
use CloudLayer\Types\StorageNotAllowedResponse;
use CloudLayer\Types\StorageParams;
use PHPUnit\Framework\Attributes\Test;

final class StorageTest extends TestCase
{
    #[Test]
    public function listStorageReturnsArray(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('storage-list.json')),
        ]);

        $items = Storage::listStorage($http);

        self::assertCount(2, $items);
        self::assertInstanceOf(StorageListItem::class, $items[0]);
    }

    #[Test]
    public function getStorageReturnsDetail(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('storage-detail.json')),
        ]);

        $detail = Storage::getStorage($http, 'storage-1');

        self::assertInstanceOf(StorageDetail::class, $detail);
        self::assertSame('My S3 Bucket', $detail->title);
    }

    #[Test]
    public function addStorageReturnsDetailOnSuccess(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('storage-detail.json')),
        ]);

        $result = Storage::addStorage($http, new StorageParams('t', 'r', 'k', 's', 'b'));

        self::assertInstanceOf(StorageDetail::class, $result);
    }

    #[Test]
    public function addStorageReturnsNotAllowed(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('storage-not-allowed.json')),
        ]);

        $result = Storage::addStorage($http, new StorageParams('t', 'r', 'k', 's', 'b'));

        self::assertInstanceOf(StorageNotAllowedResponse::class, $result);
        self::assertFalse($result->allowed);
    }

    #[Test]
    public function addStorageWithArray(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('storage-detail.json')),
        ]);

        $result = Storage::addStorage($http, [
            'title' => 't', 'region' => 'r', 'accessKeyId' => 'k',
            'secretAccessKey' => 's', 'bucket' => 'b',
        ]);

        self::assertInstanceOf(StorageDetail::class, $result);
    }

    #[Test]
    public function addStorageValidatesRequired(): void
    {
        $http = $this->createMockTransport([]);

        $this->expectException(ValidationException::class);
        Storage::addStorage($http, ['title' => '', 'region' => 'r', 'accessKeyId' => 'k', 'secretAccessKey' => 's', 'bucket' => 'b']);
    }

    #[Test]
    public function deleteStorageReturnsVoid(): void
    {
        $http = $this->createMockTransport([
            $this->mockEmptyResponse(),
        ]);

        Storage::deleteStorage($http, 'storage-1');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function deleteStorageEmptyIdThrows(): void
    {
        $http = $this->createMockTransport([]);
        $this->expectException(ValidationException::class);
        Storage::deleteStorage($http, '');
    }
}
