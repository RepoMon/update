<?php namespace Ace\Update\Domain;

use Ace\Update\Exception\FileNotFoundException;
use Github\Api\GitData\References;
use Github\Api\Repo;
use Github\Api\Search;
use Github\Client;
use Monolog\Logger;

/**
 * @author timrodger
 * Date: 23/11/15
 */
class Updater
{
    /**
     * @var Client
     */
    private $repository;

    /**
     * @var DependencyManagerInterface
     */
    private $dependency_manager;

    /**
     * @var FileSystem
     */
    private $file_system;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param GitHubRepository $repository
     * @param DependencyManagerInterface $dependency_manager
     * @param FileSystem $file_system
     * @param Logger $logger
     */
    public function __construct(
        GitHubRepository $repository,
        DependencyManagerInterface $dependency_manager,
        FileSystem $file_system,
        Logger $logger)
    {
        $this->repository = $repository;
        $this->dependency_manager = $dependency_manager;
        $this->file_system = $file_system;
        $this->logger = $logger;
    }

    /**
     * @param $from_branch
     * @param $to_branch
     * @return bool
     */
    public function run($from_branch, $to_branch)
    {
        $this->logger->debug(__METHOD__);

        $config_file = $this->repository->findFileInfo($this->dependency_manager->getConfigFileName());
        if (is_null($config_file)){
            throw new FileNotFoundException(sprintf("%s : file not found", $this->dependency_manager->getConfigFileName()));
        }

        // copy config file locally
        $config_contents = $this->repository->getFileContents($config_file['path'], $from_branch);
        $this->file_system->write($this->dependency_manager->getConfigFileName(), $config_contents);

        $lock_file = $this->repository->findFileInfo($this->dependency_manager->getLockFileName());
        if (!is_null($lock_file)) {
            // copy lock file locally
            $lock_contents = $this->repository->getFileContents($lock_file['path'], $from_branch);
            $this->file_system->write($this->dependency_manager->getLockFileName(), $lock_contents);
        }

        $new_contents = $this->dependency_manager->exec($this->file_system->getDirectory());

        if (!is_null($new_contents)) {

            $this->logger->debug('Lock file updated');

            $branch_created = $this->repository->createBranch($from_branch, $to_branch);

            if (!is_null($lock_file)) {
                // update file info on the lock file from the target branch, get its sha to use when updating it
                $lock_file = $this->repository->getFileInfo($lock_file['path'], $to_branch);
                $this->repository->updateFile($lock_file['path'], $lock_file['sha'], $new_contents, $to_branch);
            } else {
                // the lock file does not exist, create it next to config_file['path']
                $path = pathinfo($config_file['path'], PATHINFO_DIRNAME);
                $this->repository->createFile($path . '/' . $this->dependency_manager->getLockFileName(), $new_contents, $to_branch);
            }

            // only make a pr if one does not already exist
            if ($branch_created) {
                $this->repository->createPullRequest('Repository Monitor update', $from_branch, $to_branch, 'Scheduled dependency update');
            }

            return true;
        } else {
            $this->logger->debug('No changes made');
            return false;
        }
    }

    /**
     *
     */
    public function complete()
    {
        $this->logger->debug(__METHOD__);
        $this->file_system->removeDirectory();
    }
}
