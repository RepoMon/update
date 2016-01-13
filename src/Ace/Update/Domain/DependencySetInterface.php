<?php namespace Ace\Update\Domain; 
/**
 * @author timrodger
 * Date: 29/07/15
 */
interface DependencySetInterface
{

    /**
     * @param $token
     * @return null
     */
    public function setGitHubToken($token);

    /**
     * Updates dependencies to the versions specified
     *
     * @param array $versions
     * @return null
     */
    public function setRequiredVersions(array $versions);

    /**
     * Update the current dependencies
     *
     * @return null
     */
    public function updateCurrent();

}
