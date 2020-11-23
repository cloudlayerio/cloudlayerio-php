<?php


namespace CloudLayerIO\Resources;


class Url extends Resource
{

    public function __construct(string $content, array $data = [])
    {
        $this->type = 'url';
        parent::__construct($content, $data);
    }
}