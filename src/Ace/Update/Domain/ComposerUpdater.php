<?php namespace Ace\Update\Domain;

use Monolog\Logger;

/**
 * @author timrodger
 * Date: 23/11/15
 */
class ComposerUpdater
{
    /**
     *
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $full_name;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $directory;

    /**
     * @var string
     */
    private $branch;

    /**
     * @param $directory
     * @param $full_name
     * @param $token
     * @param $branch
     * @param Logger $logger
     */
    public function __construct($directory, $full_name, $token, $branch, Logger $logger)
    {
        $this->directory = $directory;
        $this->full_name = $full_name;
        $this->token = $token;
        $this->branch = $branch;
        $this->logger = $logger;
    }

    /**
     * Throws exceptions on error
     */
    public function run()
    {
        // authenticate with GH using $token
        // find composer files in remote repo
        // copy composer files to $directory
        // take an md5 sum of lock file before updating
        // run 'composer update  --prefer-dist --no-scripts'
        // check for changes - abort if no local changes
        // create a new branch from $branch
        // push new lock file to new branch

    }
}
