<?php

require_once(__DIR__.'/UpdateCommandTest.php');

use Ace\Update\Command\VersionUpdater;

/**
 * @group integration
 * @group filesystem
 * @author timrodger
 * Date: 27/07/15
 */
class VersionUpdaterTest extends UpdateCommandTest
{
    public function setUp()
    {
        parent::setUp();

        $this->createTempDirectory();
        $this->createGitRepo();
    }

    public function tearDown()
    {
        $this->cleanUpFilesystem();

        parent::tearDown();
    }

    protected function givenACommand()
    {
        $this->command = new VersionUpdater(
            $this->git_repo
        );
    }

    public function testUpdateDependencyVersions()
    {
        $this->givenACheckout();
        $this->givenACommand();

        $data = ['require' => ['symfony/symfony' => '2.7.2']];

        // commands throw exceptions on error do not return true from execute
        $this->command->execute($data);
    }
}
