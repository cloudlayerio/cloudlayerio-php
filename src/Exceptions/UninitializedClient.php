<?php


namespace CloudLayerIO\Exceptions;



class UninitializedClient extends \Exception
{
    public function __construct()
    {
        parent::__construct("Client Not initialized, Use Client::config function before using");
    }
}