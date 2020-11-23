<?php


use CloudLayerIO\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

class ClientRateLimitTest extends BaseApiTest
{

    /**
     * @covers \CloudLayerIO\Client
     * @throws \CloudLayerIO\Exceptions\InvalidConfigException
     */
    public function testRateLimit()
    {
        $apiKey = 'RANDOM_API_KEY';
        Client::config([
            'api_key' => $apiKey,
        ], $this->setMock(new MockHandler([
            new Response(200, [
                'X-RateLimit-Limit' => 10,
                'X-RateLimit-Remaining' => 10,
                'X-RateLimit-Reset' => '10',
            ], '{"status":"ok "}'),
        ]))->getHandlerStack());
        Client::getStatus();
        $this->assertSame(10,Client::getRateLimit(), 'Check Rate');
        $this->assertSame(10,Client::getRateLimitRemaining());
        $this->assertSame('10',Client::getRateLimitReset());
    }
}
