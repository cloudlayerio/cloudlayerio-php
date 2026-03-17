<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit\Api;

use CloudLayer\Api\Assets;
use CloudLayer\Errors\ValidationException;
use CloudLayer\Tests\Unit\TestCase;
use CloudLayer\Types\Asset;
use PHPUnit\Framework\Attributes\Test;

final class AssetsTest extends TestCase
{
    #[Test]
    public function listAssetsReturnsArray(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('assets-list.json')),
        ]);

        $assets = Assets::listAssets($http);

        self::assertCount(2, $assets);
        self::assertInstanceOf(Asset::class, $assets[0]);
        self::assertSame('asset-1', $assets[0]->id);
    }

    #[Test]
    public function getAssetReturnsSingle(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('asset.json')),
        ]);

        $asset = Assets::getAsset($http, 'asset-abc');

        self::assertSame('asset-abc', $asset->id);
        self::assertSame('pdf', $asset->type);
    }

    #[Test]
    public function getAssetEmptyIdThrows(): void
    {
        $http = $this->createMockTransport([]);
        $this->expectException(ValidationException::class);
        Assets::getAsset($http, '');
    }
}
