<?php

namespace Exceptions;

use CloudLayerIO\Client;
use CloudLayerIO\Exceptions\UnauthorizedUsage;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

class UnauthorizedUsageTest extends \BaseApiTest
{
    /**
     * @covers \CloudLayerIO\Exceptions\UnauthorizedUsage
     * @covers \CloudLayerIO\Client
     * @throws \CloudLayerIO\Exceptions\InvalidConfigException
     */
    public function testUnauthorizedException()
    {
        $this->expectException(UnauthorizedUsage::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Unauthorized');

        $apiKey = 'RANDOM_API_KEY';
        Client::config([
            'api_key' => $apiKey,
        ], $this->setMock(new MockHandler([
            new Response(401, [], '{"allowed":false,"statusCode":401,"reason":"Unauthorized"}'),
        ]))->getHandlerStack());

        $status = Client::getStatus();
    }

    /**
     * @covers \CloudLayerIO\Client
     * @throws \CloudLayerIO\Exceptions\InvalidConfigException
     */
    public function testOtherExceptionDoesNotThrowUnauthorized()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(404);

        $apiKey = 'RANDOM_API_KEY';
        Client::config([
            'api_key' => $apiKey,
        ], $this->setMock(new MockHandler([
            new Response(404, [], '{"allowed":false,"statusCode":404,"reason":"Not Found"}'),
        ]))->getHandlerStack());

        Client::getStatus();

    }
}
