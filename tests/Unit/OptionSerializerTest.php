<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit;

use CloudLayer\Types\ImageType;
use CloudLayer\Types\Options\Cookie;
use CloudLayer\Types\Options\HeaderFooterTemplate;
use CloudLayer\Types\Options\Margin;
use CloudLayer\Types\Options\PreviewOptions;
use CloudLayer\Types\Options\StorageRequestOptions;
use CloudLayer\Types\Options\Viewport;
use CloudLayer\Types\PdfFormat;
use CloudLayer\Utils\OptionSerializer;
use PHPUnit\Framework\Attributes\Test;

final class OptionSerializerTest extends TestCase
{
    #[Test]
    public function flatOptionsSerialized(): void
    {
        $result = OptionSerializer::serialize([
            'url' => 'https://example.com',
            'printBackground' => true,
        ]);

        self::assertSame('https://example.com', $result['url']);
        self::assertTrue($result['printBackground']);
    }

    #[Test]
    public function nullValuesStripped(): void
    {
        $result = OptionSerializer::serialize([
            'url' => 'https://example.com',
            'name' => null,
        ]);

        self::assertArrayNotHasKey('name', $result);
    }

    #[Test]
    public function enumSerializedToBackingString(): void
    {
        $result = OptionSerializer::serialize([
            'format' => PdfFormat::A4,
            'imageType' => ImageType::Png,
        ]);

        self::assertSame('a4', $result['format']);
        self::assertSame('png', $result['imageType']);
    }

    #[Test]
    public function nestedViewportSerialized(): void
    {
        $result = OptionSerializer::serialize([
            'viewPort' => new Viewport(1920, 1080),
        ]);

        self::assertIsArray($result['viewPort']);
        self::assertSame(1920, $result['viewPort']['width']);
        self::assertSame(1080, $result['viewPort']['height']);
    }

    #[Test]
    public function nestedMarginSerialized(): void
    {
        $result = OptionSerializer::serialize([
            'margin' => new Margin(top: '1cm', bottom: '2cm'),
        ]);

        self::assertIsArray($result['margin']);
        self::assertSame('1cm', $result['margin']['top']);
        self::assertSame('2cm', $result['margin']['bottom']);
    }

    #[Test]
    public function cookieArraySerialized(): void
    {
        $result = OptionSerializer::serialize([
            'cookies' => [
                new Cookie('session', 'abc123'),
                new Cookie('lang', 'en'),
            ],
        ]);

        self::assertIsArray($result['cookies']);
        self::assertCount(2, $result['cookies']);
        self::assertSame('session', $result['cookies'][0]['name']);
    }

    #[Test]
    public function storageRequestOptionsSerialized(): void
    {
        $result = OptionSerializer::serialize([
            'storage' => new StorageRequestOptions('storage-1'),
        ]);

        self::assertIsArray($result['storage']);
        self::assertSame('storage-1', $result['storage']['id']);
    }

    #[Test]
    public function booleanStoragePreserved(): void
    {
        $result = OptionSerializer::serialize([
            'storage' => true,
        ]);

        self::assertTrue($result['storage']);
    }

    #[Test]
    public function sdkOnlyFieldsStripped(): void
    {
        $result = OptionSerializer::serialize([
            'url' => 'https://example.com',
            'file' => '/path/to/file.docx',
        ]);

        self::assertArrayNotHasKey('file', $result);
    }

    #[Test]
    public function emptyArraysStripped(): void
    {
        $result = OptionSerializer::serialize([
            'url' => 'https://example.com',
            'cookies' => [],
        ]);

        self::assertArrayNotHasKey('cookies', $result);
    }

    #[Test]
    public function nestedMarginInsideHeaderFooterTemplate(): void
    {
        $result = OptionSerializer::serialize([
            'headerTemplate' => new HeaderFooterTemplate(
                method: 'template',
                margin: new Margin(top: '10px'),
            ),
        ]);

        self::assertIsArray($result['headerTemplate']);
        self::assertSame('template', $result['headerTemplate']['method']);
        self::assertIsArray($result['headerTemplate']['margin']);
        self::assertSame('10px', $result['headerTemplate']['margin']['top']);
    }

    #[Test]
    public function booleanGeneratePreviewPreserved(): void
    {
        $result = OptionSerializer::serialize(['generatePreview' => true]);
        self::assertTrue($result['generatePreview']);
    }

    #[Test]
    public function previewOptionsObjectSerialized(): void
    {
        $result = OptionSerializer::serialize([
            'generatePreview' => new PreviewOptions(width: 200, height: 150),
        ]);

        self::assertIsArray($result['generatePreview']);
        self::assertSame(200, $result['generatePreview']['width']);
    }

    #[Test]
    public function explicitNullEmulateMediaTypePreserved(): void
    {
        $result = OptionSerializer::serialize([
            'url' => 'https://example.com',
            'emulateMediaType' => null,
        ]);

        self::assertArrayHasKey('emulateMediaType', $result);
        self::assertNull($result['emulateMediaType']);
    }

    #[Test]
    public function viewPortCapitalPPreserved(): void
    {
        $result = OptionSerializer::serialize([
            'viewPort' => new Viewport(800, 600),
        ]);

        self::assertArrayHasKey('viewPort', $result);
        self::assertArrayNotHasKey('viewport', $result);
    }
}
