<?php

use Ace\Update\Command\CurrentUpdater;
use Ace\Update\Exception\DirectoryNotFoundException;

/**
 * @group unit
 * @author timrodger
 * Date: 26/07/15
 */
class CurrentUpdaterUnitTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Ace\Update\Domain\Repository
     */
    private $mock_repository;

    /**
     * @var Ace\Update\Domain\ComposerDependencySet
     */
    private $mock_dependency_set;

    /**
     * @var Ace\Update\Command\CurrentUpdater
     */
    private $command;

    /**
     * @var
     */
    private $mock_logger;

    public function setUp()
    {
        parent::setUp();
        $this->givenAMockLogger();
    }

    /**
     * @expectedException \Ace\Update\Exception\DirectoryNotFoundException
     */
    public function testExecuteThrowsExceptionIfUpdateFails()
    {
        $this->givenAMockDependencySet();
        $this->givenAMockRepository();

        $this->mock_repository->expects($this->once())
            ->method('update')
            ->will($this->throwException(new DirectoryNotFoundException()));

        $this->givenACommand();

        $data = [];

        $this->command->execute($data);
    }

    /**
     * @expectedException \Ace\Update\Exception\DirectoryNotFoundException
     */
    public function testExecuteThrowsExceptionIfCheckoutFails()
    {
        $this->givenAMockDependencySet();
        $this->givenAMockRepository();

        $this->mock_repository->expects($this->once())
            ->method('checkout')
            ->will($this->throwException(new DirectoryNotFoundException()));

        $this->givenACommand();

        $data = [];

        $this->command->execute($data);
    }

    /**
     * @expectedException \Ace\Update\Exception\FileNotFoundException
     */
    public function testExecuteThrowsExceptionIfComposerFileIsNotJson()
    {
        $this->givenAMockDependencySet();
        $this->givenAMockRepository();

        $this->mock_repository->expects($this->once())
            ->method('update');

        $this->mock_dependency_set->expects($this->once())
            ->method('updateCurrent')
            ->will($this->throwException(new \Ace\Update\Exception\FileNotFoundException()));

        $this->givenACommand();

        $data = [];

        $this->command->execute($data);
    }

    public function testExecuteUsesExistingBranchIfPresent()
    {
        $this->givenAMockDependencySet();
        $this->givenAMockRepository();

        $latest_tag = 'v2.4.0';
        $new_branch = 'feature/update-' . $latest_tag;

        $this->givenALatestTag($latest_tag);
        $this->whenABranchExists($new_branch);

        $this->mock_repository->expects($this->once())
            ->method('update');

        $this->mock_repository->expects($this->never())
            ->method('branch');

        $this->mock_repository->expects($this->once())
            ->method('checkout')
            ->with($new_branch);

        $this->mock_dependency_set->expects($this->once())
            ->method('updateCurrent');

        $this->givenACommand();

        $data = [];

        // commands throw exceptions on error, they do not return true from execute
        $this->command->execute($data);
    }

    private function whenABranchExists($branch)
    {
        $this->mock_repository->expects($this->once())
            ->method('isLocalBranch')
            ->with($branch)
            ->will($this->returnValue(true));
    }

    private function whenABranchDoesNotExist($branch)
    {
        $this->mock_repository->expects($this->once())
            ->method('isLocalBranch')
            ->with($branch)
            ->will($this->returnValue(false));
    }

    private function givenALatestTag($latest_tag)
    {
        $this->mock_repository->expects($this->any())
            ->method('getLatestTag')
            ->will($this->returnValue($latest_tag));
    }

    private function givenACommand()
    {
        $this->command = new CurrentUpdater(
            $this->mock_repository,
            $this->mock_logger
        );
    }

    private function givenAMockLogger()
    {
        $this->mock_logger = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function givenAMockDependencySet()
    {
        $this->mock_dependency_set = $this->getMockBuilder('Ace\Update\Domain\ComposerDependencySet')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function givenAMockRepository()
    {
        $this->mock_repository = $this->getMockBuilder('Ace\Update\Domain\Repository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mock_repository->expects($this->any())
            ->method('getDependencySet')
            ->will($this->returnValue($this->mock_dependency_set));
    }
}

