<?php namespace Ace\Update\Command;

use Ace\Update\Domain\Repository;

/**
 * @author timrodger
 * Date: 23/11/15
 */
abstract class UpdateCommand implements CommandInterface
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a branch from latest tag and check it out
     * Could be moved to repository class?
     * @throws \Exception
     */
    protected function createBranchFromLatestTag()
    {
        $this->repository->update();

        // generate branch name from current tag name
        $latest_tag = $this->repository->getLatestTag();
        $branch = 'feature/update-' . $latest_tag;

        // create a new branch if one is not present locally
        if (!$this->repository->isLocalBranch($branch)) {
            $this->repository->branch($branch, $latest_tag);
        }

        return $branch;
    }
}
