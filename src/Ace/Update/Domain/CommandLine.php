<?php namespace Ace\Update\Domain;

use Ace\Update\Exception\CommandExecutionException;
use Ace\Update\Exception\DirectoryNotFoundException;

/**
 * @author timrodger
 * Date: 26/07/15
 */
class CommandLine
{
    /**
     * @var string
     */
    private $directory;

    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    public function chDir($path)
    {
        if (is_dir($path)) {
            chdir($path);
            $this->directory = $path;
        }
    }

    /**
     * @param $cmd string
     * @return array of output lines
     */
    public function exec($cmd)
    {
        if (is_dir($this->directory)) {
            chdir($this->directory);
            exec($cmd, $output, $return);
            if ($return !== 0) {
                throw new CommandExecutionException("Exit code of '$cmd' was '$return''");
            }
            return $output;
        } else {
            throw new DirectoryNotFoundException("{$this->directory} is missing");
        }
    }
}
