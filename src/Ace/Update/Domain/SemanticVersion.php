<?php namespace Ace\Update\Domain;

/**
 * Provides access to the elements of a semantic version string
 */
class SemanticVersion
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var array
     */
    private $versions;

    /**
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value;

        preg_match('/(\d+)\.(\d+)\.(\d+)/', $value, $matches);

        if (count($matches) === 4) {
            $this->versions ['major'] = (integer)$matches[1];
            $this->versions ['minor'] = (integer)$matches[2];
            $this->versions ['patch'] = (integer)$matches[3];
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return is_array($this->versions);
    }

    /**
     * @return mixed
     */
    public function getMajorVersion()
    {
        return $this->versions['major'];
    }

    /**
     * @return mixed
     */
    public function getMinorVersion()
    {
        return $this->versions['minor'];
    }

    /**
     * @return mixed
     */
    public function getPatchVersion()
    {
        return $this->versions['patch'];
    }

    /**
     * Returns the actual string passed to the constructor
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * Compare this version against another
     * Return 0 if they are equal, -1 if this is less than other, 1 if this is greater than other
     *
     * use with usort($version, function($a,$b) { return $a->compare($b);});
     * @param SemanticVersion $other
     * @return integer
     */
    public function compare(SemanticVersion $other)
    {
        if ($this->getMajorVersion() > $other->getMajorVersion()) {
            return 1;
        } else if ($this->getMajorVersion() < $other->getMajorVersion()) {
            return -1;
        }

        if ($this->getMinorVersion() > $other->getMinorVersion()) {
            return 1;
        } else if ($this->getMinorVersion() < $other->getMinorVersion()) {
            return -1;
        }

        if ($this->getPatchVersion() > $other->getPatchVersion()) {
            return 1;
        } else if ($this->getPatchVersion() < $other->getPatchVersion()) {
            return -1;
        }

        return 0;
    }
}
