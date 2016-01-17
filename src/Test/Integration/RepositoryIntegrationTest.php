<?php

use Ace\Update\Domain\Repository;

/**
 * @group integration
 * @group filesystem
 *
 * @author timrodger
 * Date: 12/07/15
 */
class RepositoryIntegrationTest extends PHPUnit_Framework_TestCase
{

    /**
     * name of file in repo
     */
    const FILE_ONE = 'one.txt';

    /**
     * Other file name
     */
    const FILE_TWO = 'two.txt';

    /**
     * Another file name
     */
    const FILE_THREE = 'three.txt';

    /**
     * @var string
     */
    private $url;

    /**
     * @var
     */
    private $directory;

    /**
     * @var string
     */
    private $repo_name = 'TestRepo';

    /**
     * @var array
     */
    private $branches = ['feature/new-stuff', 'bug/bad-bug'];

    /**
     * @var array
     */
    private $tags = ['v0.1.0', 'v.1.3.4'];

    /**
     * @var Repository
     */
    private $repository;

    /**
     * 
     */
    public function setUp()
    {
        parent::setUp();

        $this->directory = TEMP_DIR;
        $this->url = $this->directory . '/Fixtures/' . $this->repo_name;

        if (!is_dir($this->directory)) {
            mkdir($this->directory);
        }

        $this->createGitRepo();
    }

    public function tearDown()
    {
        // don't use broken built in rmdir function
        exec("rm -rf " . $this->directory);

        parent::tearDown();
    }

    protected function givenACheckout()
    {
        $this->repository = new Repository($this->url, $this->directory);
        $this->repository->update();
    }

    public function testUpdate()
    {
        $this->givenACheckout();

        $parts = explode('/', $this->url);
        $name = array_pop($parts);

        $this->assertTrue(is_dir($this->directory . '/'. $name));
    }

    public function testUpdateCanBeRunMultipleTimes()
    {
        $this->givenACheckout();

        $parts = explode('/', $this->url);
        $name = array_pop($parts);

        $this->assertTrue(is_dir($this->directory . '/'. $name));

        $this->repository->update();
    }

    public function testListLocalBranches()
    {
        $this->givenACheckout();

        $local_branches = $this->repository->listLocalBranches();

        $this->assertSame(1, count($local_branches));
        $this->assertSame(['master'], $local_branches);
    }

    public function testIsLocalBranch()
    {
        $this->givenACheckout();

        $result = $this->repository->isLocalBranch('master');
        $this->assertTrue($result);

        $result = $this->repository->isLocalBranch('not-a-branch');
        $this->assertFalse($result);

        $result = $this->repository->isLocalBranch('feature/new-stuff');
        $this->assertFalse($result);
    }

    public function testIsBranch()
    {
        $this->givenACheckout();

        $result = $this->repository->isBranch('master');
        $this->assertTrue($result);

        $result = $this->repository->isBranch('not-a-branch');
        $this->assertFalse($result);
    }

    public function testListTags()
    {
        $this->givenACheckout();

        $tags = $this->repository->listTags();

        $this->assertSame(count($this->tags), count($tags));

        foreach($this->tags as $tag){
            $this->assertTrue(in_array($tag, $tags));
        }
    }

    public function testGetLatestTag()
    {
        $this->givenACheckout();

        $latest_tag = $this->repository->getLatestTag();
        $this->assertSame('v.1.3.4', $latest_tag);
    }

    public function testListAllBranches()
    {
        $this->givenACheckout();

        $branches = $this->repository->listAllBranches();

        $this->assertSame(3, count($branches));

        $this->assertTrue(in_array('master', $branches));

        foreach($this->branches as $branch){
            $this->assertTrue(in_array($branch, $branches));
        }
    }

    public function testGetFile()
    {
        $this->givenACheckout();

        $contents = $this->repository->getFile(self::FILE_ONE);

        $this->assertSame('one contents', $contents);
    }

    public function testGetFileReturnsNullForMissingFile()
    {
        $this->givenACheckout();

        $contents = $this->repository->getFile('not-there');

        $this->assertSame(null, $contents);
    }

    public function testFindFile()
    {
        $this->givenACheckout();

        $contents = $this->repository->findFile(self::FILE_ONE);

        $this->assertSame('one contents', $contents);
    }

    public function testFindFileInSubDirectory()
    {
        $this->givenACheckout();

        $contents = $this->repository->findFile(self::FILE_THREE);

        $this->assertSame('three contents', $contents);
    }

    public function testFindFileReturnsNullForMissingFile()
    {
        $this->givenACheckout();

        $contents = $this->repository->findFile('not-there');

        $this->assertSame(null, $contents);
    }

    public function testFindFilePath()
    {
        $this->givenACheckout();

        $path = $this->repository->findFilePath(self::FILE_ONE);

        $expected = (new SplFileInfo($this->directory . '/' . $this->repo_name))->getRealPath();
        $this->assertSame($expected, $path);
    }

    public function testFindFilePathInSubDirectory()
    {
        $this->givenACheckout();

        $path = $this->repository->findFilePath(self::FILE_THREE);
        $expected = (new SplFileInfo($this->directory . '/' . $this->repo_name . '/sub-dir'))->getRealPath();

        $this->assertSame($expected, $path);
    }

    public function testFindFilePathReturnsNullForMissingFile()
    {
        $this->givenACheckout();

        $path = $this->repository->findFilePath('not-there');

        $this->assertSame(null, $path);
    }

    public function testSetFileOverwritesExisting()
    {
        $this->givenACheckout();

        $new_contents = 'new contents';
        $this->repository->setFile(self::FILE_ONE, $new_contents);

        $actual = $this->repository->getFile(self::FILE_ONE);

        $this->assertSame($new_contents, $actual);
    }

    public function testSetFileCreatesNewFile()
    {
        $this->givenACheckout();

        $new_contents = 'new contents';
        $new_file = 'a-new-file.txt';
        $this->assertFileDoesNotExistInRepo($new_file);

        $this->repository->setFile($new_file, $new_contents);

        $this->assertFileExistsInRepo($new_file);

        $actual = $this->repository->getFile($new_file);
        $this->assertSame($new_contents, $actual);
    }

    public function testHasFile()
    {
        $this->givenACheckout();

        $result = $this->repository->hasFile(self::FILE_ONE);

        $this->assertTrue($result);
    }

    public function testHasFileReturnsFalseForMissingFile()
    {
        $this->givenACheckout();

        $result = $this->repository->hasFile('not-there');

        $this->assertFalse($result);
    }

    public function testRemoveFile()
    {
        $this->givenACheckout();

        $this->assertFileExistsInRepo(self::FILE_ONE);

        $this->repository->removeFile(self::FILE_ONE);

        $this->assertFileDoesNotExistInRepo(self::FILE_ONE);
    }

    public function testRemoveFileWorksIfFileDoesNotExist()
    {
        $this->givenACheckout();

        $this->assertFileDoesNotExistInRepo('four.txt');

        $this->repository->removeFile('four.txt');

        $this->assertFileDoesNotExistInRepo('four.txt');
    }

    public function testGetUrl()
    {
        $this->repository = new Repository($this->url, $this->directory);

        $result = $this->repository->getUrl();
        $this->assertSame($this->url, $result);
    }

    public function testGetId()
    {
        $expected = base64_encode($this->url);

        $this->repository = new Repository($this->url, $this->directory);

        $result = $this->repository->getId();

        $this->assertSame($expected, $result);
    }

    public function testBranch()
    {
        $name = 'feature/special-sauce';
        $this->givenACheckout();

        $result = $this->repository->isLocalBranch($name);
        $this->assertFalse($result);

        $this->repository->branch($name);

        $result = $this->repository->isLocalBranch($name);
        $this->assertTrue($result);
    }

    public function testBranchFromTag()
    {
        $name = 'feature/special-sauce';
        $this->givenACheckout();

        $result = $this->repository->isLocalBranch($name);
        $this->assertFalse($result);

        $this->repository->branch($name, $this->tags[1]);

        $result = $this->repository->isLocalBranch($name);
        $this->assertTrue($result);
    }

    public function testCheckout()
    {
        $name = 'feature/special-sauce';
        $this->givenACheckout();

        $this->repository->branch($name);
        $this->repository->checkout($name);
    }

    public function testAddFile()
    {
        $this->givenACheckout();

        $this->assertNoChanges();

        $new_contents = 'new contents';
        $this->repository->setFile(self::FILE_ONE, $new_contents);

        $this->assertFileModified(self::FILE_ONE, 0);

        $this->repository->add(self::FILE_ONE);

        $this->assertFileAdded(self::FILE_ONE, 0);
    }

    /**
     * @expectedException \Ace\Update\Exception\CommandExecutionException
     */
    public function testCommitOnUnchangedRepoThrowsException()
    {
        $this->givenACheckout();

        $this->assertNoChanges();

        $this->repository->commit('No changes');

        $this->assertNoChanges();
    }

    public function testCommit()
    {
        $this->givenACheckout();
        $new_contents = 'new contents';
        $this->repository->setFile(self::FILE_ONE, $new_contents);
        $this->repository->add(self::FILE_ONE);
        $this->assertFileAdded(self::FILE_ONE, 0);

        $this->repository->commit('Updates x');

        $this->assertNoChanges();
    }

    /**
     * If push fails The Repository will throw an exception
     * This test asserts that the push command is successful as it does not throw an exception
     */
    public function testPush()
    {
        $name = 'feature/cool-beans';
        $this->givenACheckout();
        $this->repository->branch($name);
        $this->repository->checkout($name);
        $new_contents = 'new contents';

        $this->repository->setFile(self::FILE_ONE, $new_contents);
        $this->repository->add(self::FILE_ONE);
        $this->repository->commit('Updates x');

        $this->repository->push();
    }


    private function assertNoChanges()
    {
        $status = $this->repository->status();
        $this->assertSame([], $status);
    }

    protected function assertFileModified($name, $index)
    {
        $status = $this->repository->status();
        $this->assertSame(' M ' . $name, $status[$index]);
    }

    protected function assertFileAdded($name, $index)
    {
        $status = $this->repository->status();
        $this->assertSame('M  ' . $name, $status[$index]);
    }

    protected function assertFileExistsInRepo($name)
    {
        $exists = $this->repository->hasFile($name);
        $this->assertTrue($exists);
    }

    protected function assertFileDoesNotExistInRepo($name)
    {
        $exists = $this->repository->hasFile($name);
        $this->assertFalse($exists);
    }

    private function createGitRepo()
    {
        if (!is_dir($this->url)) {
            mkdir($this->url, 0777, true);
        }

        chdir($this->url);

        file_put_contents(self::FILE_ONE, 'one contents');
        file_put_contents(self::FILE_TWO, 'two contents');

        mkdir('sub-dir', 0777, true);
        file_put_contents('sub-dir/' . self::FILE_THREE, 'three contents');

        exec("git init .");
        exec("git add .");
        exec("git commit -m 'first commit'", $output);

        foreach ($this->branches as $branch) {
            exec("git checkout -b $branch",  $output);
        }

        // then checkout master
        exec("git checkout master", $output);

        foreach($this->tags as $index => $tag){
            exec("git tag -a $tag -m 'tag number $index'");
        }
    }
}
