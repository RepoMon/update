<?php namespace Ace\Update\Command;

use Monolog\Logger;
use Ace\Update\Domain\Repository;

/**
 * @author timrodger
 * Date: 23/11/15
 */
class CurrentUpdater extends UpdateCommand
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Repository $repository
     * @param Logger $logger
     */
    public function __construct(Repository $repository, Logger $logger)
    {
        parent::__construct($repository);
        $this->logger = $logger;
    }

    /**
     * Throws exceptions on error
     *
     * @param $data
     */
    public function execute($data)
    {
        $this->logger->notice(__METHOD__ . ":: " . $this->repository->getUrl());

        $this->logger->notice(__METHOD__ . ":: is checked out ? " . $this->repository->isCheckedOut());

        $branch = $this->createBranchFromLatestTag();

        $this->logger->notice(__METHOD__ . ":: branch " . $branch);

        $this->repository->checkout($branch);

        $this->repository->getDependencySet()->updateCurrent();

        // run git commit
        $this->repository->commit('Updates current dependencies. See commit diff.');

        // run git push origin $branch
        $this->repository->push($branch);
    }
}
