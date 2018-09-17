<?php
/**
 * This file is part of the FreshFile package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2017 - 2018 by Adam Banaszkiewicz
 *
 * @license   MIT License
 * @copyright Copyright (c) 2017 - 2018, Adam Banaszkiewicz
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
    protected $saveOnDestroy;
    protected $metadata;

    public static function create($cacheFilepath, $saveOnDestroy = true)
    {
        if(self::$instance)
        {
            return self::$instance;
        }

        return self::$instance = new self($cacheFilepath, $saveOnDestroy);
    }

    public static function get()
    {
        if(! self::$instance)
        {
            return static::create(sys_get_temp_dir().'.fresh-file');
        }

        return self::$instance;
    }

    public function __construct($cacheFilepath, bool $saveOnDestroy = true)
    {
        $this->setCacheFilepath($cacheFilepath);
        $this->setSaveOnDestroy($saveOnDestroy);

        $dir = pathinfo($this->cacheFilepath, PATHINFO_DIRNAME);

        if(is_dir($dir) === false)
        {
            mkdir($dir, 0777, true);
        }
    }

    public function __destruct()
    {
        if($this->saveOnDestroy)
        {
            $this->close();
        }
    }

    /**
     * Saves metadata file on filesystem.
     */
    public function close()
    {
        $this->writeMetadataFile();
    }

    /**
     * Saves metadata file on filesystem.
     */
    public function setSaveOnDestroy(bool $bool)
    {
        $this->saveOnDestroy = $bool;

        return $this;
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

        $related = [];

        foreach($files as $file)
        {
            $related = array_merge($related, $this->getRelatedFiles($file));
        }

        $files = array_unique(array_merge($files, $related));

        $anyIsFresh = false;

        foreach($files as $file)
        {
            if($clearstatcache)
            {
                clearstatcache(false, $file);
            }

            $ct = $this->getFilemtimeCurrent($file);
            $mt = $this->getFilemtimeMetadata($file);

            if($ct > $mt)
            {
                $anyIsFresh = true;
            }

            $this->setFilemtime($file, $ct);
        }

        return $anyIsFresh;
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
    public function getFilemtimeMetadata($file, $default = 0)
    {
        $this->readMetadataFile();

        return isset($this->metadata[$file]['mt']) ? $this->metadata[$file]['mt'] : $default;
    }

    /**
     * Sets file modification time in metadata.
     * @param string  $file      Filepath
     * @param integer $filemtime Modification time in unix timestamp.
     */
    public function setFilemtime($file, $filemtime)
    {
        $this->readMetadataFile();

        $this->metadata[$file]['mt'] = $filemtime;

        return $this;
    }

    /**
     * Sets related $files array for given $file.
     * @param string $file  Filepath of target relation.
     * @param array  $files Array of related filepaths.
     */
    public function setRelatedFiles($file, $files)
    {
        $this->metadata[$file]['rel'] = $files;

        return $this;
    }

    /**
     * Returns related $files from given $file.
     * @param  string $file    Filepath fo target relation.
     * @param  array  $default Default related files.
     * @return array           Array of related files saved last time.
     */
    public function getRelatedFiles($file, $default = [])
    {
        $this->readMetadataFile();

        return isset($this->metadata[$file]['rel']) ? $this->metadata[$file]['rel'] : $default;
    }

    /**
     * Returns filepath to metadata file.
     * @return string
     */
    public function getCacheFilepath()
    {
        return $this->cacheFilepath;
    }

    /**
     * Set filepath to metadata file.
     * @param string $filepath
     */
    public function setCacheFilepath($filepath)
    {
        $this->cacheFilepath = $filepath;

        return $this;
    }

    public function writeMetadataFile()
    {
        if(is_array($this->metadata))
        {
            $directory = pathinfo($this->getCacheFilepath(), PATHINFO_DIRNAME);

            if(is_dir($directory) === false)
            {
                mkdir($directory, 0777, true);
            }

            file_put_contents($this->getCacheFilepath(), serialize($this->metadata));
        }

        return $this;
    }

    public function readMetadataFile()
    {
        if($this->metadata === null && is_file($this->getCacheFilepath()))
        {
            $this->metadata = (array) @ unserialize(file_get_contents($this->getCacheFilepath()));
        }

        return $this;
    }
}
