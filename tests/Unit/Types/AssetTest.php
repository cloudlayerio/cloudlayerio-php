<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit\Types;

use CloudLayer\Tests\Unit\TestCase;
use CloudLayer\Types\Asset;
use PHPUnit\Framework\Attributes\Test;

final class AssetTest extends TestCase
{
    #[Test]
    public function fromArrayWithFullData(): void
    {
        $data = $this->loadFixture('asset.json');
        $asset = Asset::fromArray($data);

        self::assertSame('user-456', $asset->uid);
        self::assertSame('asset-abc', $asset->id);
        self::assertSame('file-123', $asset->fileId);
        self::assertSame('pdf', $asset->type);
        self::assertSame(102400, $asset->size);
    }

    #[Test]
    public function fromArrayWithMinimalData(): void
    {
        $asset = Asset::fromArray(['uid' => 'u1', 'fileId' => 'f1']);

        self::assertSame('u1', $asset->uid);
        self::assertNull($asset->id);
        self::assertNull($asset->type);
        self::assertNull($asset->name);
    }

    #[Test]
    public function fromArrayIgnoresUnknownFields(): void
    {
        $asset = Asset::fromArray(['uid' => 'u1', 'fileId' => 'f1', 'unknownField' => 'value']);
        self::assertSame('u1', $asset->uid);
    }
}
