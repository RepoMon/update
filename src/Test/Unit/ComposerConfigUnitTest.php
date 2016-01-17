<?php

use Ace\Update\Domain\ComposerConfig;

/**
 * @group unit
 * @author timrodger
 * Date: 10/07/15
 */
class ComposerConfigUnitTest extends PHPUnit_Framework_TestCase
{

    public function testHasDependencyReturnsFalseWhenItsNotThere()
    {
        $config = [];
        $lock = [];
        $name = 'company/repo';
        $composer = new ComposerConfig($config, $lock);

        $result = $composer->hasDependency($name);

        $this->assertFalse($result);
    }

    public function testHasDependencyReturnsTrueWhenItsThere()
    {
        $name = 'company/repo';
        $config = ['require' => [$name => '1.0.0']];
        $lock = [];

        $composer = new ComposerConfig($config, $lock);

        $result = $composer->hasDependency($name);

        $this->assertTrue($result);
    }

    public function testHasDependencyReturnsTrueWhenItsADevDependency()
    {
        $name = 'company/repo';
        $config = ['require-dev' => [$name => '1.0.0']];
        $lock = [];

        $composer = new ComposerConfig($config, $lock);

        $result = $composer->hasDependency($name);

        $this->assertTrue($result);
    }

    public function testGetDependencyVersionReturnsNullWhenItsNotThere()
    {
        $config = [];
        $lock = [];
        $name = 'company/repo';
        $composer = new ComposerConfig($config, $lock);

        $result = $composer->getDependencyVersion($name);

        $this->assertNull($result);
    }

    public function testGetDependencyVersionReturnsVersionWhenItsThere()
    {
        $lock = [];
        $name = 'company/repo';
        $version = '1.0.0';
        $config = ['require' => [$name => $version]];
        $composer = new ComposerConfig($config, $lock);

        $result = $composer->getDependencyVersion($name);

        $this->assertSame($version, $result);
    }

    public function testGetDependencyVersionReturnsVersionWhenItsADevDependency()
    {
        $lock = [];
        $name = 'company/repo';
        $version = '1.0.0';
        $config = ['require-dev' => [$name => $version]];
        $composer = new ComposerConfig($config, $lock);

        $result = $composer->getDependencyVersion($name);

        $this->assertSame($version, $result);
    }

    public function testGetLockVersionReturnsNullWhenItsNotThere()
    {
        $config = [];
        $lock = [];
        $name = 'company/repo';
        $composer = new ComposerConfig($config, $lock);

        $result = $composer->getLockVersion($name);

        $this->assertNull($result);
    }

    public function testGetLockVersionReturnsVersionWhenItsThere()
    {
        $config = [];
        $name = 'company/repo';
        $version = '1.0.0';
        $lock = ["packages" => [
            ['name' => $name, 'version' => $version, 'time' => "2015-07-10 06:54:46"]
        ]];

        $composer = new ComposerConfig($config, $lock);

        $result = $composer->getLockVersion($name);

        $this->assertSame($version, $result);
    }

    public function testGetLockVersionReturnsVersionWhenItsADevDependency()
    {
        $config = [];
        $name = 'company/repo';
        $version = '1.0.0';
        $lock = ["packages-dev" => [
            ['name' => $name, 'version' => $version, 'time' => "2015-07-10 06:54:46"]
        ]];

        $composer = new ComposerConfig($config, $lock);

        $result = $composer->getLockVersion($name);

        $this->assertSame($version, $result);
    }

    public function testGetLockDateReturnsTheLockDate()
    {
        $config = [];
        $name = 'company/repo';
        $version = '1.0.0';
        $time = "2015-07-10 06:54:46";
        $lock = ["packages-dev" => [
            ['name' => $name, 'version' => $version, 'time' => $time]
        ]];

        $composer = new ComposerConfig($config, $lock);

        $result = $composer->getLockDate($name);

        $this->assertSame($time, $result);
    }

    public function testSetRequireVersionOverwritesExistingVersion()
    {
        $lock = [];
        $name = 'company/repo';
        $version = '1.0.0';
        $config = ['require' => [$name => $version]];
        $composer = new ComposerConfig($config, $lock);

        $new_version = '2.8.2';
        $composer->setRequireVersion($name, $new_version);

        $actual = $composer->getDependencyVersion($name);

        $this->assertSame($new_version, $actual);
    }

    public function testSetRequireVersionAddsRequireIfMissing()
    {
        $lock = [];
        $name = 'company/repo';
        $config = [];
        $composer = new ComposerConfig($config, $lock);

        $new_version = '2.8.2';
        $composer->setRequireVersion($name, $new_version);

        $actual = $composer->getDependencyVersion($name);

        $this->assertSame($new_version, $actual);
    }

    public function testSetRequireVersionAddsVersion()
    {
        $lock = [];
        $name = 'company/repo';
        $version = '1.0.0';
        $config = ['require' => [$name => $version]];
        $composer = new ComposerConfig($config, $lock);

        $new_name = 'company-a/repo-x';
        $new_version = '2.8.2';
        $exists = $composer->hasDependency($new_name);
        $this->assertFalse($exists);

        $composer->setRequireVersion($new_name, $new_version);

        $actual = $composer->getDependencyVersion($new_name);

        $this->assertSame($new_version, $actual);
    }

    public function testSetRequireDevVersionOverwritesExistingVersion()
    {
        $lock = [];
        $name = 'company/repo';
        $version = '1.0.0';
        $config = ['require-dev' => [$name => $version]];
        $composer = new ComposerConfig($config, $lock);

        $new_version = '2.8.2';
        $composer->setRequireDevVersion($name, $new_version);

        $actual = $composer->getDependencyVersion($name);

        $this->assertSame($new_version, $actual);
    }

    public function testSetRequireDevVersionAddsRequireDevIfMissing()
    {
        $lock = [];
        $name = 'company/repo';
        $config = [];
        $composer = new ComposerConfig($config, $lock);

        $new_version = '2.8.2';
        $composer->setRequireDevVersion($name, $new_version);

        $actual = $composer->getDependencyVersion($name);

        $this->assertSame($new_version, $actual);
    }

    public function testSetRequireDevVersionAddsVersion()
    {
        $lock = [];
        $name = 'company/repo';
        $version = '1.0.0';
        $config = ['require-dev' => [$name => $version]];
        $composer = new ComposerConfig($config, $lock);

        $new_name = 'company-a/repo-x';
        $new_version = '2.8.2';
        $composer->setRequireDevVersion($new_name, $new_version);

        $actual = $composer->getDependencyVersion($new_name);

        $this->assertSame($new_version, $actual);
    }


    public function testGetComposerJson()
    {
        $lock = [];
        $name = 'company/repo';
        $version = '1.0.0';
        $config = ['require-dev' => [$name => $version]];
        $composer = new ComposerConfig($config, $lock);

        $this->assertSame($config, $composer->getComposerJson());
    }

}
