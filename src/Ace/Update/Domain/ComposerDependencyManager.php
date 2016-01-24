<?php namespace Ace\Update\Domain;

/**
 * Uses composer to re-generate a composer.lock file
 */
class ComposerDependencyManager implements DependencyManagerInterface
{

    public function getConfigFileName()
    {
        return 'composer.json';
    }

    public function getLockFileName()
    {
        return 'composer.lock';
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

        // take an md5 sum of lock file before updating
        if (file_exists($lock_file)) {
            $lock_sum = md5_file($lock_file);
        }

        exec('composer update  --prefer-dist --no-scripts', $output, $success);

        // check for changes - return file contents if it's different
        if (md5_file($lock_file) !== $lock_sum){
            return file_get_contents($lock_file);
        }
        return null;
    }
}