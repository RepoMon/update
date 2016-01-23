<?php namespace Ace\Update\Domain;

/**
 * @author timrodger
 * Date: 23/01/2016
 */
class NpmDependencyManager
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

        // take an md5 sum of lock file before updating
        if (file_exists($lock_file)) {
            $lock_sum = md5_file($lock_file);
        }

        exec('npm install', $output, $success);
        exec('npm shrinkwrap', $output, $success);

        // check for changes - return file contents if it's different
        if (md5_file($lock_file) !== $lock_sum){
            return file_get_contents($lock_file);
        }
        return null;
    }
}