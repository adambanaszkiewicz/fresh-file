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

        $this->unlinkCacheFile();

        file_put_contents($ff->getCacheFilepath(), serialize($this->getImaginaryFileMetadata()));

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        $this->unlinkCacheFile();
    }

    public function testWriteFileMetadata()
    {
        $imaginaryFile = $this->getImaginaryFilepath();
        $ff = new FreshFile($this->getCacheFilepath());

        $this->unlinkCacheFile();

        file_put_contents($ff->getCacheFilepath(), serialize($this->getImaginaryFileMetadata()));
        file_put_contents($imaginaryFile, 'test');

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        unset($ff);

        $ff = new FreshFile($this->getCacheFilepath());

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        $this->unlinkCacheFile();
        unlink($imaginaryFile);
    }

    public function testReadFilemtime()
    {
        $imaginaryFile = $this->getImaginaryFilepath();
        $ff = new FreshFile($this->getCacheFilepath());

        $this->unlinkCacheFile();

        file_put_contents($ff->getCacheFilepath(), serialize($this->getImaginaryFileMetadata()));
        file_put_contents($imaginaryFile, 'test');

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        unset($ff);

        $mtime = time();
        touch($imaginaryFile, $mtime);

        $ff = new FreshFile($this->getCacheFilepath());

        $this->assertEquals($mtime, $ff->getFilemtimeCurrent($imaginaryFile));

        $this->unlinkCacheFile();
        unlink($imaginaryFile);
    }

    public function testIsFresh()
    {
        $imaginaryFile = $this->getImaginaryFilepath();
        $ff = new FreshFile($this->getCacheFilepath());

        $this->unlinkCacheFile();

        file_put_contents($ff->getCacheFilepath(), serialize($this->getImaginaryFileMetadata()));
        file_put_contents($imaginaryFile, 'test');

        $this->assertEquals('11111', $ff->getFilemtimeMetadata($imaginaryFile));

        unset($ff);

        $mtime = time();
        touch($imaginaryFile, $mtime);

        $ff = new FreshFile($this->getCacheFilepath());

        $this->assertTrue($ff->isFresh($imaginaryFile));
        $this->assertFalse($ff->isFresh($imaginaryFile));

        $this->unlinkCacheFile();
        unlink($imaginaryFile);
    }

    public function testStoreRelatedFiles()
    {
        $imaginaryFile = $this->getImaginaryFilepath();
        $ff = new FreshFile($this->getCacheFilepath());
        file_put_contents($imaginaryFile, 'test');

        $relatedFiles = [];

        for($i = 0; $i < 5; $i++)
        {
            $relatedFiles[] = $imaginaryFile.'-'.$i;
            file_put_contents($imaginaryFile.'-'.$i, 'data-'.$i);
        }

        $ff->setRelatedFiles($imaginaryFile, $relatedFiles);
        $ff->writeMetadataFile();

        // First call sets filemtimes, we do not want to assert this.
        $ff->isFresh($imaginaryFile);

        // Now this should returns true - all related files are fresh.
        $this->assertFalse($ff->isFresh($imaginaryFile));

        // Touch main file should returns true
        touch($imaginaryFile, time() + 10);
        $this->assertTrue($ff->isFresh($imaginaryFile));
        $this->assertFalse($ff->isFresh($imaginaryFile));

        // Touch of any related file also should returns true
        touch($relatedFiles[1], time() + 10);
        $this->assertTrue($ff->isFresh($imaginaryFile));
        $this->assertFalse($ff->isFresh($imaginaryFile));

        $this->assertEquals($relatedFiles, $ff->getRelatedFiles($imaginaryFile));

        foreach($relatedFiles as $file)
            if(is_file($file))
                unlink($file);

        $this->unlinkCacheFile();
        unlink($imaginaryFile);
    }

    public function testSaveOnDestroy()
    {
        $this->unlinkCacheFile();
        $this->assertFalse($this->cacheFileExists());

        $imaginaryFile = $this->getImaginaryFilepath();
        $ff = new FreshFile($this->getCacheFilepath());
        file_put_contents($imaginaryFile, 'test');

        // Force refresh metadata.
        $ff->isFresh($imaginaryFile);

        $this->assertFalse($this->cacheFileExists());
        unset($ff);
        $this->assertTrue($this->cacheFileExists());

        $this->unlinkCacheFile();
        unlink($imaginaryFile);

        $this->assertFalse($this->cacheFileExists());
    }

    public function testPreventSaveOnDestroy()
    {
        $this->unlinkCacheFile();
        $this->assertFalse($this->cacheFileExists());

        $imaginaryFile = $this->getImaginaryFilepath();
        $ff = new FreshFile($this->getCacheFilepath(), false);
        file_put_contents($imaginaryFile, 'test');

        // Force refresh metadata.
        $ff->isFresh($imaginaryFile);

        $this->assertFalse($this->cacheFileExists());
        unset($ff);
        $this->assertFalse($this->cacheFileExists());

        $this->unlinkCacheFile();
        unlink($imaginaryFile);

        $this->assertFalse($this->cacheFileExists());
    }

    protected function getCacheFilepath()
    {
        return __DIR__.'/cache/.requtize.fresh-file';
    }

    protected function getImaginaryFilepath()
    {
        return __DIR__.'/file.test';
    }

    protected function getImaginaryFileMetadata()
    {
        return [ $this->getImaginaryFilepath() => [
            'mt'  => '11111',
            'rel' => []
        ]];
    }

    protected function cacheFileExists()
    {
        clearstatcache();

        return is_file($this->getCacheFilepath());
    }

    protected function unlinkCacheFile()
    {
        if(is_file($this->getCacheFilepath()))
            unlink($this->getCacheFilepath());
    }
}
