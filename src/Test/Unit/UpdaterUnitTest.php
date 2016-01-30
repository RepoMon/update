<?php

use Ace\Update\Domain\Updater;

/**
 * @group unit
 * @author timrodger
 * Date: 26/07/15
 */
class UpdaterUnitTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    private $mock_logger;

    private $mock_repository;

    private $mock_dependency_manager;

    private $mock_file_system;

    private $updater;

    public function setUp()
    {
        parent::setUp();

        $this->givenAMockGitHubRepository();
        $this->givenAMockDependencyManager();
        $this->givenAMockFileSystem();
        $this->givenAMockLogger();

        $this->givenAnUpdater();
    }

    /**
     * @expectedException Ace\Update\Exception\FileNotFoundException
     */
    public function testRunThrowsExceptionWhenConfigFileIsNotFound()
    {
        $this->mock_repository->expects($this->any())
            ->method('findFileInfo')
            ->will($this->returnValue(null));

        $this->updater->run('master', 'auto-update');
    }

    /**
     * @expectedException GitHub\Exception\RuntimeException
     */
    public function testRunThrowsExceptionWhenFindConfigFileFails()
    {
        $this->mock_repository->expects($this->any())
            ->method('findFileInfo')
            ->will($this->throwException(new Github\Exception\RuntimeException));

        $this->updater->run('master', 'auto-update');
    }

    /**
     * @expectedException GitHub\Exception\RuntimeException
     */
    public function testRunThrowsExceptionWhenCreateBranchFails()
    {
        $this->mock_repository->expects($this->any())
            ->method('findFileInfo')
            ->will($this->returnValue(['path' => '/composer.json', 'sha' => 'abcde']));

        $this->mock_dependency_manager->expects($this->once())
            ->method('exec')
            ->will($this->returnValue('the new contents'));

        $this->mock_repository->expects($this->once())
            ->method('createBranch')
            ->will($this->throwException(new Github\Exception\RuntimeException));

        $this->updater->run('master', 'auto-update');
    }

    /**
     * @expectedException GitHub\Exception\RuntimeException
     */
    public function testRunThrowsExceptionWhenCreateFileFails()
    {
        $this->mock_repository->expects($this->any())
            ->method('findFileInfo')
            ->will($this->returnValue(['path' => '/composer.json', 'sha' => 'abcde']));

        $this->mock_dependency_manager->expects($this->once())
            ->method('exec')
            ->will($this->returnValue('the new contents'));

        $this->mock_repository->expects($this->once())
            ->method('createBranch');

        $this->mock_repository->expects($this->once())
            ->method('updateFile')
            ->will($this->throwException(new Github\Exception\RuntimeException));

        $this->updater->run('master', 'auto-update');
    }

    public function testRunCreatesPR()
    {
        $this->mock_repository->expects($this->any())
            ->method('findFileInfo')
            ->will($this->returnValue(['path' => '/composer.json', 'sha' => 'abcde']));

        $this->mock_dependency_manager->expects($this->once())
            ->method('exec')
            ->will($this->returnValue('the new contents'));

        $this->mock_repository->expects($this->once())
            ->method('createBranch')
            ->will($this->returnValue(true));

        $this->mock_repository->expects($this->never())
            ->method('createFile');

        $this->mock_repository->expects($this->once())
            ->method('updateFile');

        $this->mock_repository->expects($this->once())
            ->method('createPullRequest');

        $result = $this->updater->run('master', 'auto-update');

        $this->assertTrue($result);

    }

    public function testRunCreatesLockFile()
    {
        $this->mock_repository->expects($this->any())
            ->method('findFileInfo')
            ->will($this->onConsecutiveCalls(
                ['path' => '/composer.json', 'sha' => 'abcde'],
                null
                )
            );

        $this->mock_dependency_manager->expects($this->once())
            ->method('exec')
            ->will($this->returnValue('the new contents'));

        $this->mock_repository->expects($this->once())
            ->method('createBranch')
            ->will($this->returnValue(true));

        $this->mock_repository->expects($this->once())
            ->method('createFile');

        $this->mock_repository->expects($this->never())
            ->method('updateFile');

        $this->mock_repository->expects($this->once())
            ->method('createPullRequest');

        $result = $this->updater->run('master', 'auto-update');

        $this->assertTrue($result);

    }

    public function testRunDoesNotCreateSecondPR()
    {
        $this->mock_repository->expects($this->any())
            ->method('findFileInfo')
            ->will($this->returnValue(['path' => '/composer.json', 'sha' => 'abcde']));

        $this->mock_dependency_manager->expects($this->once())
            ->method('exec')
            ->will($this->returnValue('the new contents'));

        // branch already exists
        $this->mock_repository->expects($this->once())
            ->method('createBranch')
            ->will($this->returnValue(false));

        $this->mock_repository->expects($this->once())
            ->method('updateFile');

        $this->mock_repository->expects($this->never())
            ->method('createFile');

        $this->mock_repository->expects($this->never())
            ->method('createPullRequest');

        $result = $this->updater->run('master', 'auto-update');

        $this->assertTrue($result);

    }

    public function testRunReturnsFalseWhenNoChangesAreMade()
    {
        $this->mock_repository->expects($this->any())
            ->method('findFileInfo')
            ->will($this->returnValue(['path' => '/composer.json', 'sha' => 'abcde']));

        $this->mock_dependency_manager->expects($this->once())
            ->method('exec')
            ->will($this->returnValue(null));

        $this->mock_repository->expects($this->never())
            ->method('createBranch');

        $this->mock_repository->expects($this->never())
            ->method('updateFile');

        $this->mock_repository->expects($this->never())
            ->method('createFile');

        $this->mock_repository->expects($this->never())
            ->method('createPullRequest');

        $result = $this->updater->run('master', 'auto-update');

        $this->assertFalse($result);

    }

    private function givenAnUpdater()
    {
        $this->updater = new Updater(
            $this->mock_repository,
            $this->mock_dependency_manager,
            $this->mock_file_system,
            $this->mock_logger
        );
    }

    private function givenAMockGitHubRepository()
    {
        $this->mock_repository = $this->getMockBuilder('Ace\Update\Domain\GitHubRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function givenAMockDependencyManager()
    {
        $this->mock_dependency_manager = $this->getMockBuilder('Ace\Update\Domain\DependencyManagerInterface')
            ->getMock();
    }

    private function givenAMockFileSystem()
    {
        $this->mock_file_system = $this->getMockBuilder('Ace\Update\Domain\FileSystem')
            ->disableOriginalConstructor()
            ->getMock();
    }
    private function givenAMockLogger()
    {
        $this->mock_logger = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();
    }
}

