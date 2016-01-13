<?php namespace Ace\Update\Domain;

/**
 * Represents a composer config contents & its lock file contents
 *
 * eg running composer update etc
 */
class ComposerConfig
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $lock;

    /**
     * @param array $config
     * @param array $lock
     */
    public function __construct(array $config, array $lock)
    {
        $this->config = $config;
        $this->lock = $lock;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasDependency($name)
    {
        $dependencies = $this->getDependencies();

        if (isset($dependencies[$name])){
            return true;
        }

        return false;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getDependencyVersion($name)
    {
        if ($this->hasDependency($name)){
            $dependencies = $this->getDependencies();
            return $dependencies[$name];
        }
    }

    /**
     * @param $name string name of dependency
     * @param $version string version value
     */
    public function setRequireVersion($name, $version)
    {
        if (!isset($this->config['require'])) {
            $this->config['require'] = [];
        }
        $this->config['require'][$name] = $version;
    }

    /**
     * @param $name string name of dependency
     * @param $version string version value
     */
    public function setRequireDevVersion($name, $version)
    {
        if (!isset($this->config['require-dev'])) {
            $this->config['require-dev'] = [];
        }
        $this->config['require-dev'][$name] = $version;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getLockVersion($name)
    {
        $lock_dependencies = $this->getLockDependencies();
        if (isset($lock_dependencies[$name])){
            return $lock_dependencies[$name]['version'];
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getLockDate($name)
    {
        $lock_dependencies = $this->getLockDependencies();
        if (isset($lock_dependencies[$name])){
            return $lock_dependencies[$name]['time'];
        }
    }

    /**
     * Return a flat array of dependencies and versions
     * @return array
     */
    public function getDependencies()
    {
        $dependencies = [];

        if (isset($this->config['require'])){
            $dependencies = $this->config['require'];
        }

        if (isset($this->config['require-dev'])){
            $dependencies = array_merge($dependencies, $this->config['require-dev']);
        }

        return $dependencies;
    }

    /**
     * @return array
     */
    public function getLockDependencies()
    {
        $dependencies = [];

        if (isset($this->lock['packages'])) {
            foreach ($this->lock['packages'] as $package) {
                $dependencies[$package['name']] = [
                    'version' => $package['version'],
                    'time' => $package['time']
                ];
            }
        }

        if (isset($this->lock['packages-dev'])) {
            foreach ($this->lock['packages-dev'] as $package) {
                $dependencies[$package['name']] = [
                    'version' => $package['version'],
                    'time' => $package['time']
                ];
            }
        }

        return $dependencies;
    }

    /**
     * @return array
     */
    public function getComposerJson()
    {
        return $this->config;
    }
}
