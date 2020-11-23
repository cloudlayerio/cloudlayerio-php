<?php


namespace CloudLayerIO\Exceptions;


use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UnauthorizedUsage extends ClientException
{

    public function __construct(ClientException $exception)
    {
        $response = json_decode($exception->getResponse()->getBody());
        parent::__construct($response->reason, $exception->getRequest(), $exception->getResponse(), $exception->getPrevious(), $exception->getHandlerContext());
    }
}