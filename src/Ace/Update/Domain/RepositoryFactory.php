<?php namespace Ace\Update\Domain; 
/**
 * @author timrodger
 * Date: 16/12/15
 */
class RepositoryFactory
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


    public function create($repository_url, $token)
    {
        return new Repository(
            $repository_url,
            $this->repository_dir,
            $token
        );
    }
}
