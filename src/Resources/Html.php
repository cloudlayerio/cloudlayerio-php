<?php


namespace CloudLayerIO\Resources;


class Html extends Resource
{

    public function __construct(string $content, array $data = [])
    {
        $this->type = 'html';
        parent::__construct($content, $data);
    }

    public function getContent(): string
    {
        return base64_encode($this->content);
    }
}