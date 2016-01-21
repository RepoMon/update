<?php namespace Ace\Update\Domain;

use Monolog\Logger;

/**
 * @author timrodger
 * Date: 26/07/15
 */
class UpdaterFactory
{
    /**
     * @var string
     */
    private $repository_dir;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param $repository_dir
     */
    public function __construct($repository_dir, Logger $logger)
    {
        $this->repository_dir = $repository_dir;
        $this->logger = $logger;
    }

    /**
     * @param $full_name
     * @param $token
     * @param $branch
     * @return ComposerUpdater
     */
    public function create($full_name, $token, $branch)
    {
        $this->logger->notice(__METHOD__ . "$full_name, $token, $branch");
        return new ComposerUpdater($this->repository_dir, $full_name, $token, $branch, $this->logger);
    }
}
