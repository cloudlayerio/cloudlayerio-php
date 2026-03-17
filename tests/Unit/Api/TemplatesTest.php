<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit\Api;

use CloudLayer\Api\Templates;
use CloudLayer\Tests\Unit\TestCase;
use CloudLayer\Types\PublicTemplate;
use PHPUnit\Framework\Attributes\Test;

final class TemplatesTest extends TestCase
{
    #[Test]
    public function listTemplatesUsesV2Path(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('templates-list.json')),
        ], $history);

        $templates = Templates::listTemplates($http);

        self::assertCount(2, $templates);
        self::assertInstanceOf(PublicTemplate::class, $templates[0]);

        $request = $this->getLastRequest($history);
        self::assertSame('/v2/templates', $request->getUri()->getPath());
    }

    #[Test]
    public function listTemplatesWithFilterOptions(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, []),
        ], $history);

        Templates::listTemplates($http, ['type' => 'invoice', 'expand' => true]);

        $request = $this->getLastRequest($history);
        $query = $request->getUri()->getQuery();
        self::assertStringContainsString('type=invoice', $query);
    }

    #[Test]
    public function getTemplateUsesV2SingularPath(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('template.json')),
        ], $history);

        $template = Templates::getTemplate($http, 'tmpl-123');

        self::assertSame('tmpl-123', $template->id);

        $request = $this->getLastRequest($history);
        self::assertSame('/v2/template/tmpl-123', $request->getUri()->getPath());
    }

    #[Test]
    public function templateEndpointsWorkWithV1Client(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('templates-list.json')),
        ], $history, apiVersion: 'v1');

        Templates::listTemplates($http);

        $request = $this->getLastRequest($history);
        // Should use /v2/templates regardless of client apiVersion
        self::assertSame('/v2/templates', $request->getUri()->getPath());
    }
}
