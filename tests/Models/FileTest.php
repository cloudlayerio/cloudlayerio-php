<?php

namespace Models;

use CloudLayerIO\Models\File;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;


class FileTest extends \BaseApiTest
{

    protected vfsStreamDirectory $fileSystemMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileSystemMock = vfsStream::setup();
    }

    /**
     * @covers \CloudLayerIO\Models\File
     */
    public function testSave()
    {
        $file = new File('RANDOM-CONTENT');
        $filename = 'image.jpg';
        $this->fakeFile($filename);
        $result = $file->save($this->fileSystemMock->url() . '/' . $filename);
        $this->assertSame(true, $result, "check that save function returns true");
    }

    /**
     * @covers \CloudLayerIO\Models\File
     */
    public function testGetContent()
    {
        $content = 'RANDOM-CONTENT';
        $file = new File($content);
        $this->assertSame($content, $file->getContent());
    }

    public function fakeFile($fileName)
    {
        $fileMock = new vfsStreamFile($fileName);
        $this->fileSystemMock->addChild($fileMock);
    }

}
