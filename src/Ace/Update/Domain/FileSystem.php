<?php namespace Ace\Update\Domain;

/**
 * @author timrodger
 * Date: 23/01/2016
 */
class FileSystem
{
    /**
     * @var string
     */
    private $directory;

    /**
     * FileSystem constructor.
     * @param $directory
     */
    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     *
     */
    public function makeDir()
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }
    }

    /**
     * @param $name
     * @param $contents
     * @return string
     */
    public function write($name, $contents)
    {
        $file = $this->directory . '/' . $name;
        file_put_contents($file, $contents);

        return $file;
    }
}