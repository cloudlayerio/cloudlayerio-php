<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;


abstract class BaseApiTest extends TestCase
{
    protected MockHandler $mock;

    protected array $httpRequestContainer = [];

    function getHandlerStack()
    {
        $this->httpRequestContainer = [];
        $httpRequestHistory = Middleware::history($this->httpRequestContainer);
        $handlerStack = HandlerStack::create($this->mock);
        $handlerStack->push($httpRequestHistory);
        return $handlerStack;
    }

    /**
     * @param MockHandler|null $mock
     * @return BaseApiTest
     */
    public function setMock(?MockHandler $mock = null)
    {
        $this->mock = $mock ?? new MockHandler([
                new Response(200, [], '{}'),
            ]);
        return $this;
    }

    public function lastRequest()
    {
        return array_pop($this->httpRequestContainer)['request'];
    }

}