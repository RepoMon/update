<?php

use Ace\Update\Domain\SemanticVersion;

/**
 * @group unit
 * @author timrodger
 * Date: 12/07/15
 */
class SemanticVersionUnitTest extends PHPUnit_Framework_TestCase
{

    public function getVersionData()
    {
        return [
            ['v1.0.0', 1, 0, 0],
            ['0.7.3', 0, 7, 3],
            ['0.0.0 ', 0, 0, 0],
            ['3.1.4a ', 3, 1, 4],
        ];
    }

    /**
     * @dataProvider getVersionData
     * @param $value
     * @param $major
     * @param $minor
     * @param $patch
     */
    public function testGetMajorVersion($value, $major, $minor, $patch)
    {
        $sem_ver = new SemanticVersion($value);
        $this->assertSame($major, $sem_ver->getMajorVersion());
    }

    /**
     * @dataProvider getVersionData
     * @param $value
     * @param $major
     * @param $minor
     * @param $patch
     */
    public function testGetMinorVersion($value, $major, $minor, $patch)
    {
        $sem_ver = new SemanticVersion($value);
        $this->assertSame($minor, $sem_ver->getMinorVersion());
    }

    /**
     * @dataProvider getVersionData
     * @param $value
     * @param $major
     * @param $minor
     * @param $patch
     */
    public function testGetPatchVersion($value, $major, $minor, $patch)
    {
        $sem_ver = new SemanticVersion($value);
        $this->assertSame($patch, $sem_ver->getPatchVersion());
    }

    public function getInvalidVersionValues()
    {
        return [
            ['not a semantic version string'],
            ['v1.6a.4'],
            ['1v.6.4'],
            ['1.5.a21v']
        ];
    }

    /**
     * @dataProvider getInvalidVersionValues
     * @param $invalid
     */
    public function testSemVerHandleInvalidInput($invalid)
    {
        $sem_ver = new SemanticVersion($invalid);
        $this->assertFalse($sem_ver->isValid());
    }

    /**
     * @dataProvider getInvalidVersionValues
     * @param $invalid
     */
    public function testGetMajorVersionReturnsNullForInvalidInput($invalid)
    {
        $sem_ver = new SemanticVersion($invalid);
        $this->assertNull($sem_ver->getMajorVersion());
    }

    /**
     * @dataProvider getInvalidVersionValues
     * @param $invalid
     */
    public function testGetMinorVersionReturnsNullForInvalidInput($invalid)
    {
        $sem_ver = new SemanticVersion($invalid);
        $this->assertNull($sem_ver->getMinorVersion());
    }

    /**
     * @dataProvider getInvalidVersionValues
     * @param $invalid
     */
    public function testGetPatchVersionReturnsNullForInvalidInput($invalid)
    {
        $sem_ver = new SemanticVersion($invalid);
        $this->assertNull($sem_ver->getPatchVersion());
    }

    /**
     * @dataProvider getVersionData
     * @param $value
     */
    public function testToStringReturnsOriginalString($value)
    {
        $sem_ver = new SemanticVersion($value);
        $this->assertSame($value, (string)$sem_ver);
    }

    /**
     * @dataProvider getVersionData
     * @param $version
     */
    public function testCompareReturnsZeroForEqualVersions($version)
    {
        $sem_ver = new SemanticVersion($version);

        $result = $sem_ver->compare($sem_ver);
        $this->assertSame(0, $result);
    }

    public function getLowerVersionFirst()
    {
        return [
            ['v1.0.0', 'v.2.0.0'],
            ['1.1.1', '1.1.2'],
            ['1.2.9', '1.3.0']
        ];
    }

    /**
     * @dataProvider getLowerVersionFirst
     * @param $version_a
     * @param $version_b
     */
    public function testCompareReturnsMinusOneForLowerVersion($version_a, $version_b)
    {
        $sem_ver_a = new SemanticVersion($version_a);
        $sem_ver_b = new SemanticVersion($version_b);

        $result = $sem_ver_a->compare($sem_ver_b);
        $this->assertSame(-1, $result);
    }

    public function getHigherVersionFirst()
    {
        return [
            ['v2.0.0', 'v.1.0.0'],
            ['v2.0.0', 'v1.9.16'],
            ['v1.10.0', 'v1.1.0'],
            ['1.1.2', '1.1.1'],
            ['1.3.0', '1.2.9']
        ];
    }

    /**
     * @dataProvider getHigherVersionFirst
     * @param $version_a
     * @param $version_b
     */
    public function testCompareReturnsOneForHigherVersion($version_a, $version_b)
    {
        $sem_ver_a = new SemanticVersion($version_a);
        $sem_ver_b = new SemanticVersion($version_b);

        $result = $sem_ver_a->compare($sem_ver_b);
        $this->assertSame(1, $result);
    }
}
