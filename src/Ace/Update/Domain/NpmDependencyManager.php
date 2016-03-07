<?php namespace Ace\Update\Domain;

/**
 * Uses shrinkwrap to lock a package.json file at a particular version
 */
class NpmDependencyManager implements DependencyManagerInterface
{

    public function getConfigFileName()
    {
        return 'package.json';
    }

    public function getLockFileName()
    {
        return 'npm-shrinkwrap.json';
    }

    /**
     * @param $directory
     * @return null|string
     */
    public function exec($directory)
    {
        chdir($directory);

        $lock_file = $directory . '/' . $this->getLockFileName();
        $lock_sum = '';

        // take an md5 sum of lock file before updating it
        if (file_exists($lock_file)) {
            $lock_sum = md5_file($lock_file);
        }

        // clear cache to reduce diff churn
        exec('npm cache clean', $output, $success);

        // update to get the latest versions from package.json
        exec('npm update', $output, $success);

        // regenerate the shrinkwrap file
        exec('npm-shrinkwrap', $output, $success);

        // check for changes - return file contents if it's different
        if (md5_file($lock_file) !== $lock_sum){
            return file_get_contents($lock_file);
        }

        return null;
    }
}