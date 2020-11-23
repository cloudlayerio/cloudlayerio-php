<?php

namespace Resources;

use BaseApiTest;
use CloudLayerIO\Client;
use CloudLayerIO\Resources\Html;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

class HtmlTest extends BaseApiTest
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
     * @covers \CloudLayerIO\Resources\Html
     * @covers \CloudLayerIO\Resources\Resource
     */
    public function testHtmlToImage()
    {
        $url = new Html('<html><body>Test</body></html>');

        //send request to convert url to image
        $url->toImage();

        $lastRequest = $this->lastRequest();
        $this->assertEquals('/v1/html/image', $lastRequest->getUri()->getPath(), "Check that converting html to image is sent on endpoint /v1/url/image");

    }

    /**
     * @covers \CloudLayerIO\Resources\Html
     * @covers \CloudLayerIO\Resources\Resource
     */
    public function testHtmlToPdf()
    {
        $url = new Html('<html><body>Test</body></html>');

        //send request to convert url to PDF
        $url->toPdf();

        $lastRequest = $this->lastRequest();
        $this->assertEquals('/v1/html/pdf', $lastRequest->getUri()->getPath(), "Check that converting html to pdf is sent on endpoint /v1/url/pdf");

    }

    /**
     * @covers \CloudLayerIO\Resources\Html
     * @covers \CloudLayerIO\Resources\Resource
     */
    public function testHtmlIsAddedOnRequestBodyAsBase64()
    {
        $code = '<html><body>Test</body></html>';
        $html = new Html($code);

        //send request to convert url to image
        $image = $html->toImage();

        $lastRequest = $this->lastRequest();
        $requestBody = json_decode($lastRequest->getBody());
        $this->assertEquals(base64_encode($code), $requestBody->html, 'Check that provided Html was added as base64 encoded string on request body parameter');
    }
}
