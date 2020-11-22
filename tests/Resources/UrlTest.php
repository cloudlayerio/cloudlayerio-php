<?php

namespace Resources;

use BaseApiTest;
use CloudLayerIO\Client;
use CloudLayerIO\Resources\Url;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

class UrlTest extends BaseApiTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $apiKey = 'RANDOM_API_KEY';
        Client::config(
            ['api_key' => $apiKey],
            $this->setMock(
                new MockHandler([new Response(200, [], 'RANDOM-CONTENT')])
            )
                ->getHandlerStack()
        );
    }

    /**
     * @covers \CloudLayerIO\Resources\Url
     * @covers \CloudLayerIO\Resources\Resource
     */
    public function testUrlToImage()
    {
        $url = new Url('http://example.com');

        //send request to convert url to image
        $url->toImage();

        $lastRequest = $this->lastRequest();
        $this->assertEquals('/v1/url/image', $lastRequest->getUri()->getPath(), "Check that converting url to image is sent on endpoint /v1/url/image");


    }

    /**
     * @covers \CloudLayerIO\Resources\Url
     * @covers \CloudLayerIO\Resources\Resource
     */
    public function testUrlToPdf()
    {
        $url = new Url('http://example.com');

        //send request to convert url to PDF
        $url->toPdf();

        $lastRequest = $this->lastRequest();
        $this->assertEquals('/v1/url/pdf', $lastRequest->getUri()->getPath(), "Check that converting url to pdf is sent on endpoint /v1/url/pdf");


    }

    /**
     * @covers \CloudLayerIO\Resources\Url
     * @covers \CloudLayerIO\Resources\Resource
     */
    public function testUrlIsAddedOnRequestBody()
    {
        $link = 'http://example.com';
        $url = new Url($link);

        //send request to convert url to image
        $url->toImage();

        $lastRequest = $this->lastRequest();
        $requestBody = json_decode($lastRequest->getBody());
        $this->assertEquals($link,$requestBody->url, 'Check that provided url was added as request body parameter');
    }

}
