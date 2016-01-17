<?php namespace Ace\Update\Command;

use Ace\Update\Domain\Repository;
use Monolog\Logger;

/**
 * @author timrodger
 * Date: 26/07/15
 */
class UpdateCommandFactory
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
     * @param $url
     * @param $language
     * @param $dependency_manager
     * @param $token
     * @return CurrentUpdater
     */
    public function create($url, $language, $dependency_manager, $token)
    {
        $this->logger->notice(__METHOD__ . ' ' . $this->repository_dir);

        $repository = new Repository(
            $url,
            $this->repository_dir,
            $token
        );

        return new CurrentUpdater($repository, $this->logger);
    }
}
