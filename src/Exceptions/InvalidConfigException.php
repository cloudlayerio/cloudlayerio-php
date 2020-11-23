<?php


namespace CloudLayerIO\Exceptions;



class InvalidConfigException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Invalid Config parameters");
    }
}