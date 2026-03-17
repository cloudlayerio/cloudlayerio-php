<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit\Types;

use CloudLayer\Tests\Unit\TestCase;
use CloudLayer\Types\StorageDetail;
use CloudLayer\Types\StorageListItem;
use CloudLayer\Types\StorageNotAllowedResponse;
use CloudLayer\Types\StorageParams;
use PHPUnit\Framework\Attributes\Test;

final class StorageTest extends TestCase
{
    #[Test]
    public function storageListItemFromArray(): void
    {
        $item = StorageListItem::fromArray(['id' => 's1', 'title' => 'My Bucket']);
        self::assertSame('s1', $item->id);
        self::assertSame('My Bucket', $item->title);
    }

    #[Test]
    public function storageDetailFromArray(): void
    {
        $data = $this->loadFixture('storage-detail.json');
        $detail = StorageDetail::fromArray($data);

        self::assertSame('storage-1', $detail->id);
        self::assertSame('My S3 Bucket', $detail->title);
        self::assertSame('user-456', $detail->uid);
        self::assertSame('encrypted-storage-config-data', $detail->data);
    }

    #[Test]
    public function storageNotAllowedFromArray(): void
    {
        $data = $this->loadFixture('storage-not-allowed.json');
        $response = StorageNotAllowedResponse::fromArray($data);

        self::assertFalse($response->allowed);
        self::assertStringContainsString('does not support', $response->reason);
        self::assertSame(403, $response->statusCode);
    }

    #[Test]
    public function storageParamsToArrayWithEndpoint(): void
    {
        $params = new StorageParams('My Bucket', 'us-east-1', 'AKIA...', 'secret', 'my-bucket', 'https://s3.custom.com');
        $arr = $params->toArray();

        self::assertSame('My Bucket', $arr['title']);
        self::assertSame('us-east-1', $arr['region']);
        self::assertSame('https://s3.custom.com', $arr['endpoint']);
    }

    #[Test]
    public function storageParamsToArrayWithoutEndpoint(): void
    {
        $params = new StorageParams('t', 'r', 'k', 's', 'b');
        $arr = $params->toArray();

        self::assertArrayNotHasKey('endpoint', $arr);
    }
}
