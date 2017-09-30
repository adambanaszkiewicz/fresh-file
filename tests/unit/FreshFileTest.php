<?php

use Requtize\FreshFile\FreshFile;

class FreshFileTest extends PHPUnit_Framework_TestCase
{
    public function testCreateCache()
    {
        $this->assertEquals($this->getCacheFilepath(), (new FreshFile($this->getCacheFilepath()))->getCacheFilepath());

        FreshFile::create($this->getCacheFilepath());

        $this->assertEquals($this->getCacheFilepath(), FreshFile::get()->getCacheFilepath());
    }

    public function testReadFileMetadata()
    {
        $imaginaryFile = $this->getImaginaryFilepath();
        $ff = new FreshFile($this->getCacheFilepath());

        $this->unlinkCacheFile($ff);

        file_put_contents($ff->getCacheFilepath(), serialize($this->getImaginaryFileMetadata()));

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        $this->unlinkCacheFile($ff);
    }

    public function testWriteFileMetadata()
    {
        $imaginaryFile = $this->getImaginaryFilepath();
        $ff = new FreshFile($this->getCacheFilepath());

        $this->unlinkCacheFile($ff);

        file_put_contents($ff->getCacheFilepath(), serialize($this->getImaginaryFileMetadata()));
        file_put_contents($imaginaryFile, 'test');

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        unset($ff);

        $ff = new FreshFile($this->getCacheFilepath());

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        $this->unlinkCacheFile($ff);
        unlink($imaginaryFile);
    }

    public function testReadFilemtime()
    {
        $imaginaryFile = $this->getImaginaryFilepath();
        $ff = new FreshFile($this->getCacheFilepath());

        $this->unlinkCacheFile($ff);

        file_put_contents($ff->getCacheFilepath(), serialize($this->getImaginaryFileMetadata()));
        file_put_contents($imaginaryFile, 'test');

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        unset($ff);

        $mtime = time();
        touch($imaginaryFile, $mtime);

        $ff = new FreshFile($this->getCacheFilepath());

        $this->assertEquals($mtime, $ff->getFilemtimeCurrent($imaginaryFile));

        $this->unlinkCacheFile($ff);
        unlink($imaginaryFile);
    }

    public function testIsFresh()
    {
        $imaginaryFile = $this->getImaginaryFilepath();
        $ff = new FreshFile($this->getCacheFilepath());

        $this->unlinkCacheFile($ff);

        file_put_contents($ff->getCacheFilepath(), serialize($this->getImaginaryFileMetadata()));
        file_put_contents($imaginaryFile, 'test');

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        unset($ff);

        $mtime = time();
        touch($imaginaryFile, $mtime);

        $ff = new FreshFile($this->getCacheFilepath());

        $this->assertEquals(false, $ff->isFresh($imaginaryFile));
        $this->assertEquals(true, $ff->isFresh($imaginaryFile));

        $this->unlinkCacheFile($ff);
        unlink($imaginaryFile);
    }

    protected function getCacheFilepath()
    {
        return __DIR__.DIRECTORY_SEPARATOR.'.requtize.fresh-file';
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
