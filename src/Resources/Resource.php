<?php


namespace CloudLayerIO\Resources;


use CloudLayerIO\Client;
use GuzzleHttp\RequestOptions;
use CloudLayerIO\Models\File;

abstract class Resource
{
    protected string $content;

    protected string $type;

    private array $data;


    public function __construct(string $content, array $data)
    {
        $this->content = $content;
        $this->data = $data;
    }

    public function toImage(): File
    {
        $response = Client::post($this->type . '/image', [
            RequestOptions::JSON => $this->getData(),
        ]);
        return new File($response->getBody());
    }

    public function toPdf(): File
    {
        $response = Client::post($this->type . '/pdf', [
            RequestOptions::JSON => $this->getData(),
        ]);
        return new File($response->getBody());
    }

    private function getData()
    {
        return array_merge($this->data, [$this->type => $this->getContent()]);
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}