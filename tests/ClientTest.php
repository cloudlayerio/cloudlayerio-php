<?php


use CloudLayerIO\Client;
use CloudLayerIO\Exceptions\InvalidConfigException;
use CloudLayerIO\Exceptions\UninitializedClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

class ClientTest extends BaseApiTest
{
    /**
     * @covers \CloudLayerIO\Client
     * @covers \CloudLayerIO\Exceptions\UninitializedClient
     */
    public function testUninitializedClientThrowsException()
    {
        $this->expectException(UninitializedClient::class);
        Client::resetClient();
        Client::getStatus();
    }

    /**
     * @covers \CloudLayerIO\Client
     * @throws InvalidConfigException
     */
    public function testValidConfigWorks()
    {
        $apiKey = 'RANDOM_API_KEY';
        Client::config([
            'api_key' => $apiKey,
        ], $this->setMock()->getHandlerStack());

        $response = Client::get('getStatus');
        $lastRequest = $this->lastRequest();
        $this->assertEquals('GET', $lastRequest->getMethod(), 'Assert Request made was GET method');
        $this->assertEquals($apiKey, $lastRequest->getHeaders()['x-api-key'][0], 'Assert correct x-api-key was added to header');
    }

    /**
     * @covers \CloudLayerIO\Client
     */
    public function testGetStatusOk()
    {
        $apiKey = 'RANDOM_API_KEY';
        Client::config([
            'api_key' => $apiKey,
        ], $this->setMock(new MockHandler([
            new Response(200, [], '{"status":"ok "}'),
        ]))->getHandlerStack());

        $status = Client::getStatus();
        $this->assertSame(true,$status, 'Check getStatus return boolean true for status ok');
    }

    /**
     * @covers \CloudLayerIO\Client
     */
    public function testGetStatusNotOk()
    {
        $apiKey = 'RANDOM_API_KEY';
        Client::config([
            'api_key' => $apiKey,
        ], $this->setMock(new MockHandler([
            new Response(200, [], '{"status":"something else"}'),
        ]))->getHandlerStack());

        $status = Client::getStatus();
        $this->assertSame(false,$status, 'Check getStatus return boolean false for status anything other than ok');
    }

    /**
     * @covers \CloudLayerIO\Exceptions\InvalidConfigException
     * @covers \CloudLayerIO\Client
     * @throws InvalidConfigException
     */
    public function testInValidConfigFails()
    {
        $this->expectException(InvalidConfigException::class);
        Client::config([]);
    }

    /**
     * @covers \CloudLayerIO\Client
     * @throws InvalidConfigException
     */
    public function testConfigWithCustomUrlWorks()
    {
        $customUrl = "http://exampl.com/v1/";
        Client::config([
            'api_key' => "RANDOM_API_KEY",
            'base_uri' => $customUrl,

        ], $this->setMock()->getHandlerStack());

        $response = Client::get('getStatus');
        $lastRequest = $this->lastRequest();
        $this->assertEquals($customUrl . 'getStatus',
            (string)$lastRequest->getUri()
            , 'Assert Custom base_uri config works');
    }

    /**
     * @covers \CloudLayerIO\Client
     * @throws InvalidConfigException
     */
    public function testCallToUndefinedMethodOnClientThrowsException()
    {
        $this->expectException(\Exception::class);
        $apiKey = 'RANDOM_API_KEY';
        Client::config([
            'api_key' => $apiKey,
        ], $this->setMock()->getHandlerStack());
        Client::randomMethodNameThatIsNotDefined();
    }

}
