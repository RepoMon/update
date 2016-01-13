<?php namespace Ace\Update\Command;

use Ace\Update\Domain\Repository;

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
     * @param $repository_dir
     */
    public function __construct($repository_dir)
    {
        $this->repository_dir = $repository_dir;
    }

    /**
     * @param $url
     * @param $language
     * @param $dependency_manager
     * @param $token
     * @return CurrentUpdater
     */
    public function create($url,  $language, $dependency_manager, $token)
    {
        $repository = new Repository(
            $url,
            $this->repository_dir,
            $token
        );

        return new CurrentUpdater($repository);
    }
}
