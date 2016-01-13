<?php namespace Ace\Update\Command;

/**
 * @author timrodger
 * Date: 23/11/15
 */
class CurrentUpdater extends UpdateCommand
{
    /**
     * Throws exceptions on error
     *
     * @param $data
     */
    public function execute($data)
    {
        $branch = $this->createBranchFromLatestTag();

        $this->repository->checkout($branch);

        $this->repository->getDependencySet()->updateCurrent();

        // run git commit
        $this->repository->commit('Updates current dependencies. See commit diff.');

        // run git push origin $branch
        $this->repository->push($branch);
    }
}
