<?php namespace Ace\Update\Command;

/**
 * Update the dependency versions of a repository
 *
 *  Branches from latest tag
 *  Installs the updates
 *  Commits changes
 *  Pushes new branch to origin
 */
class VersionUpdater extends UpdateCommand
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

        $this->repository->getDependencySet()->setRequiredVersions($data['require']);

        // run git commit
        $this->repository->commit('Updates required dependency versions. See commit diff.');

        // run git push origin $branch
        $this->repository->push($branch);
    }
}
