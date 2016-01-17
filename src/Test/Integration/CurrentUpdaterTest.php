<?php

require_once(__DIR__.'/UpdateCommandTest.php');

use Ace\Update\Command\CurrentUpdater;

/**
 * @group integration
 * @group filesystem
 * @author timrodger
 * Date: 27/07/15
 */
class CurrentUpdaterTest extends UpdateCommandTest
{

    private $mock_logger;

    public function setUp()
    {
        parent::setUp();

        $this->createTempDirectory();
        $this->createGitRepo();
        $this->givenAMockLogger();
    }

    public function tearDown()
    {
        $this->cleanUpFilesystem();

        parent::tearDown();
    }



    public function testUpdateCurrent()
    {
        $this->givenACheckout();
        $this->givenACommand();

        // commands throw exceptions on error do not return true from execute
        $this->command->execute(null);
    }

    private function givenACommand()
    {
        $this->command = new CurrentUpdater(
            $this->git_repo,
            $this->mock_logger
        );
    }

    private function givenAMockLogger()
    {
        $this->mock_logger = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
