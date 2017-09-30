<?php

use Requtize\FreshFile\FreshFile;

class FreshFileTest extends PHPUnit_Framework_TestCase
{
    public function testCreateCache()
    {
        $this->assertEquals(__DIR__.'/.requtize.fresh-file', (new FreshFile(__DIR__))->getCacheFilepath());
        $this->assertEquals(__DIR__.'/.requtize.fresh-file', FreshFile::get(__DIR__)->getCacheFilepath());
    }

    public function testReadFileMetadata()
    {
        $imaginaryFile = $this->getImaginaryFilepath();
        $ff = new FreshFile(__DIR__);

        $this->unlinkCacheFile($ff);

        file_put_contents($ff->getCacheFilepath(), serialize($this->getImaginaryFileMetadata()));

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        $this->unlinkCacheFile($ff);
    }

    public function testWriteFileMetadata()
    {
        $imaginaryFile = $this->getImaginaryFilepath();
        $ff = new FreshFile(__DIR__);

        $this->unlinkCacheFile($ff);

        file_put_contents($ff->getCacheFilepath(), serialize($this->getImaginaryFileMetadata()));
        file_put_contents($imaginaryFile, 'test');

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        unset($ff);

        $ff = new FreshFile(__DIR__);

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        $this->unlinkCacheFile($ff);
        unlink($imaginaryFile);
    }

    public function testReadFilemtime()
    {
        $imaginaryFile = $this->getImaginaryFilepath();
        $ff = new FreshFile(__DIR__);

        $this->unlinkCacheFile($ff);

        file_put_contents($ff->getCacheFilepath(), serialize($this->getImaginaryFileMetadata()));
        file_put_contents($imaginaryFile, 'test');

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        unset($ff);

        $mtime = time();
        touch($imaginaryFile, $mtime);

        $ff = new FreshFile(__DIR__);

        $this->assertEquals($mtime, $ff->getFilemtimeCurrent($imaginaryFile));

        $this->unlinkCacheFile($ff);
        unlink($imaginaryFile);
    }

    public function testIsFresh()
    {
        $imaginaryFile = $this->getImaginaryFilepath();
        $ff = new FreshFile(__DIR__);

        $this->unlinkCacheFile($ff);

        file_put_contents($ff->getCacheFilepath(), serialize($this->getImaginaryFileMetadata()));
        file_put_contents($imaginaryFile, 'test');

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        unset($ff);

        $mtime = time();
        touch($imaginaryFile, $mtime);

        $ff = new FreshFile(__DIR__);

        $this->assertEquals(false, $ff->isFresh($imaginaryFile));
        $this->assertEquals(true, $ff->isFresh($imaginaryFile));

        $this->unlinkCacheFile($ff);
        unlink($imaginaryFile);
    }

    protected function getImaginaryFilepath()
    {
        return __DIR__.'/file.test';
    }

    protected function getImaginaryFileMetadata()
    {
        return [ $this->getImaginaryFilepath() => '11111' ];
    }

    protected function unlinkCacheFile(FreshFile $ff)
    {
        if(is_file($ff->getCacheFilepath()))
            unlink($ff->getCacheFilepath());
    }
}
