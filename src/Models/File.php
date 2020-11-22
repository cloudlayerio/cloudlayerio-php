<?php


namespace CloudLayerIO\Models;


class File
{
    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function save(string $file)
    {
        $resource = fopen($file, 'w');
        fwrite($resource, $this->getContent());
        fclose($resource);
        return true;
    }

    public function getContent(): string
    {
        return $this->content;
    }

}