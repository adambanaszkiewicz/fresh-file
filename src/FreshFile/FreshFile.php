<?php
/**
 * This file is part of the FreshFile package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2017 by Adam Banaszkiewicz
 *
 * @license   MIT License
 * @copyright Copyright (c) 2017, Adam Banaszkiewicz
 * @link      https://github.com/requtize/fresh-file
 */

namespace Requtize\FreshFile;

/**
 * @author Adam Banaszkiewicz https://github.com/requtize
 */
class FreshFile
{
    protected static $instance;
    protected $cacheFilepath;
    protected $metadata;

    public static function create($cacheFilepath)
    {
        if(self::$instance)
        {
            return self::$instance;
        }

        return self::$instance = new self($cacheFilepath);
    }

    public static function get()
    {
        return self::$instance;
    }

    public function __construct($cacheFilepath)
    {
        $this->cacheFilepath = $cacheFilepath ? $cacheFilepath : __DIR__.'/.requtize.fresh-file';
    }

    public function __destruct()
    {
        $this->writeMetadataFile();
    }

    /**
     * Allows set this instance as main instance of static property.
     * Usage singleton is wrong, but here we can change singleton object
     * any time.
     * @return FreshFile
     */
    public function setThisInstanceAsMain()
    {
        return self::$instance = $this;
    }

    /**
     * Checks if any of file is fresh.
     * @param  mixed  $files Filepath or array of filepaths.
     * @return boolean       If any of given files is not fresh, return false.
     */
    public function isFresh($files, $clearstatcache = false)
    {
        if(is_array($files) === false)
        {
            $files = [ $files ];
        }

        $allFresh = true;

        foreach($files as $file)
        {
            if($clearstatcache)
                clearstatcache(false, $file);

            $ct = $this->getFilemtimeCurrent($file);
            $mt = $this->getFilemtimeMetadata($file);

            if($ct > $mt)
                $allFresh = false;

            $this->metadata[$file] = $ct;
        }

        return $allFresh;
    }

    /**
     * Returns file modification time.
     * @param  string $file Filepath to check
     * @return int|bool False on error. Integer when filemtime success.
     */
    public function getFilemtimeCurrent($file)
    {
        if(is_readable($file) === false)
            return false;

        $time = filemtime($file);

        return $time ? $time : false;
    }

    /**
     * Returns existent filemtime from cache file.
     * @param  string $file Filepath to check.
     * @return int If returns 0 (zero) that means there is
     *             no info about this file in metadata yet.
     */
    public function getFilemtimeMetadata($file)
    {
        $this->readMetadataFile();

        return isset($this->metadata[$file]) ? $this->metadata[$file] : 0;
    }

    /**
     * Returns filepath to metadata file.
     * @return string
     */
    public function getCacheFilepath()
    {
        return $this->cacheFilepath;
    }

    protected function readMetadataFile()
    {
        if($this->metadata === null)
        {
            $this->metadata = unserialize(file_get_contents($this->getCacheFilepath()));
        }
    }

    protected function writeMetadataFile()
    {
        if(is_array($this->metadata))
        {
            file_put_contents($this->getCacheFilepath(), serialize($this->metadata));
        }
    }
}
