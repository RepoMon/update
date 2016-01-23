<?php namespace Ace\Update\Domain;

use Github\Api\GitData\References;
use Github\Api\Repo;
use Github\Api\Search;
use Github\Client;
use Monolog\Logger;
use ErrorException;
use SplFileInfo;

/**
 * @author timrodger
 * Date: 23/11/15
 */
class ComposerUpdater
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $full_name;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $directory;

    /**
     * @var string
     */
    private $branch;

    /**
     * @param $directory
     * @param $full_name
     * @param $token
     * @param $branch
     * @param Logger $logger
     */
    public function __construct(Client $client, $directory, $full_name, $token, $branch, Logger $logger)
    {
        $this->client = $client;
        $this->directory = $directory;
        $this->full_name = $full_name;
        $this->token = $token;
        $this->branch = $branch;
        $this->logger = $logger;
    }

    /**
     * Throws exceptions on error
     */
    public function run()
    {
        try {
            // authenticate with GH using $token
            $this->client->authenticate($this->token, Client::AUTH_HTTP_TOKEN);

            $this->logger->info('Authenticated');

            $composer_json = $this->findFile($this->client->api('search'), '.json');
            if (is_null($composer_json)){
                $this->logger->info('No composer.json file found');
                return;
            }
            $composer_lock = $this->findFile($this->client->api('search'), '.lock');

            mkdir($this->directory . '/' . $this->full_name, 0777, true);

            // copy composer files to $directory
            $dir = $this->copyFile($this->client->api('repo'), $composer_json['path']);
            if ($composer_lock) {
                $this->copyFile($this->client->api('repo'), $composer_lock['path']);
            }

            $new_lock_contents = $this->runDependencyManager($dir);

            if (!is_null($new_lock_contents)) {
                $branch = 'repo-man-update';
                $new_branch = $this->createBranch($this->client->api('git')->references(), $branch);

                // push new lock file to new branch
                list($owner, $name) = explode('/', $this->full_name);

                $this->client->api('repo')->contents()->update(
                    $owner,
                    $name,
                    $composer_lock['path'],
                    $new_lock_contents,
                    'Auto updates composer.lock',
                    $composer_lock['sha'],
                    $branch,
                    ['name' => 'Dependency monitor', 'email' => 'robot@dep-mon.net']
                );


            } else {
                $this->logger->info('No changes to composer.lock were made');
            }

        } catch (ErrorException $ex){
            $this->logger->error(get_class($ex) . ' ' .$ex->getMessage());
        }
    }

    /**
     * @todo return the file contents not the path? but we need the path later to update it
     *
     * @param Search $search
     * @param $extension
     * @return mixed
     */
    private function findFile(Search $search, $extension)
    {
        $query = sprintf('repo:%s extension:%s', $this->full_name, $extension);
        $this->logger->info(__METHOD__ . ' ' . $query);

        $files = $search->code($query);

        foreach ($files as $file) {
            $this->logger->info('Found .json file ' . $file['path']);
            if ($file['name'] = 'composer' . $extension){
                return $file;
            }
        }
        return null;
    }

    /**
     * @param Repo $repo
     * @param $file_path
     * @return string
     */
    private function copyFile(Repo $repo, $file_path)
    {
        $this->logger->info(__METHOD__ . ' ' . $file_path);


        if (!is_null($file_path)) {
            list($owner, $name) = explode('/', $this->full_name);
            $contents = $repo->contents()->download($owner, $name, '/' . $file_path, $this->branch);

            $file = $this->directory . '/' . $this->full_name . '/' . $file_path;
            file_put_contents($file, $contents);
            // return the path to the new file
            $info = new SplFileInfo($file);
            return $info->getPath();
        }
        return null;
    }

    /**
     * @param $directory
     * @return bool
     */
    private function runDependencyManager($directory)
    {
        $this->logger->info(__METHOD__ . ' ' . $directory);

        chdir($directory);

        // take an md5 sum of lock file before updating
        $lock_sum = md5_file($directory . '/composer.lock');
        exec('composer update  --prefer-dist --no-scripts', $output, $success);
        // check for changes - abort if no local changes
        $new_lock = md5_file($directory . '/composer.lock');

        // return true to indicate a change in the file
        if($new_lock !== $lock_sum){
            return file_get_contents($directory . '/composer.lock');
        }
        return null;
    }

    /**
     * @param References $references
     * @param $name
     * @return null|string
     */
    private function createBranch(References $references, $name)
    {
        // create a new branch from $branch
        list($owner, $repo) = explode('/', $this->full_name);

        $new_branch = 'refs/heads/' . $name;
        // get the sha of this->branch to create a branch from
        $branches = $references->branches($owner, $repo);
        foreach ($branches as $branch) {
            if ($branch['ref'] === 'refs/heads/' . $this->branch){
                $references->create($owner, $repo, ['ref' => $new_branch, 'sha' => $branch['object']['sha']]);
                $this->logger->info(__METHOD__ . ' created ' . $new_branch);
                return $new_branch;
            }
        }

        $this->logger->error(__METHOD__ . ' did not find  ' . $this->branch);
        return null;
    }
}
