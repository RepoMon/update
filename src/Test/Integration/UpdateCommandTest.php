<?php

use Ace\Update\Domain\Repository as GitRepo;
use Ace\Update\Command\CommandInterface;

/**
 * @group integration
 * @group filesystem
 * @author timrodger
 */
abstract class UpdateCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * name of file in repo
     */
    const FILE_ONE = 'composer.json';

    /**
     * Other file name
     */
    const FILE_TWO = 'two.txt';

    /**
     * @var string
     */
    protected $url;

    /**
     * @var
     */
    protected $directory;

    /**
     * @var string
     */
    protected $repo_name = 'TestRepo';

    /**
     * @var array
     */
    protected $tags = ['v0.0.1'];

    /**
     * @var GitRepo
     */
    protected $git_repo;

    /**
     * @var CommandInterface
     */
    protected $command;

    /**
     *
     */
    protected function createTempDirectory()
    {
        $this->directory = TEMP_DIR;
        $this->url = $this->directory . '/Fixtures/' . $this->repo_name;

        if (!is_dir($this->directory)) {
            mkdir($this->directory);
        }
    }

    protected function cleanUpFilesystem()
    {
        // don't use broken built in rmdir function
        exec("rm -rf " . $this->directory);
    }

    protected function givenACheckout()
    {
        $this->git_repo = new GitRepo($this->url, $this->directory);
    }

    protected function createGitRepo()
    {
        if (!is_dir($this->url)) {
            mkdir($this->url, 0777, true);
        }

        chdir($this->url);

        file_put_contents(self::FILE_ONE, json_encode(['require' => ['behat/behat' => '2.5.3']]));
        file_put_contents(self::FILE_TWO, 'two contents');

        exec("git init .");
        exec("git add .");
        exec("git commit -m 'first commit'", $output);

        foreach($this->tags as $index => $tag){
            exec("git tag -a $tag -m 'tag number $index'");
        }
    }
}
