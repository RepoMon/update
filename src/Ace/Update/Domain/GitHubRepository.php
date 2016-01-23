<?php namespace Ace\Update\Domain;

use Ace\Update\Exception\BranchNotFoundException;
use Github\Client;
use Monolog\Logger;

/**
 * @author timrodger
 * Date: 23/01/2016
 */
class GitHubRepository
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $full_name;

    /**
     * @var string
     */
    private $token;

    /**
     * @var Logger
     */
    private $logger;

    private $account_name = 'Dependency monitor';

    private $account_email = 'robot@dep-mon.net';

    /**
     * @param Client $client
     * @param string $full_name
     * @param string $token
     * @param Logger $logger
     */
    public function __construct(Client $client, $full_name, $token, Logger $logger)
    {
        $this->client = $client;
        $this->full_name = $full_name;
        $this->token = $token;
        $this->logger = $logger;
    }

    /**
     * @param $name
     * @param $email
     */
    public function setAccountDetails($name, $email)
    {
        $this->account_name = $name;
        $this->account_email = $email;
    }

    /**
     *
     */
    public function authenticate()
    {
        $this->client->authenticate($this->token, Client::AUTH_HTTP_TOKEN);
        $this->logger->info('Authenticated');
    }

    /**
     * @param $file_name
     * @return array|null
     */
    public function findFileInfo($file_name)
    {
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $query = sprintf('repo:%s extension:.%s', $this->full_name, $extension);

        $this->logger->info(__METHOD__ . ' ' . $query);

        $files = $this->client->api('search')->code($query);

        if (isset($files['items'])) {
            foreach ($files['items'] as $file) {
                if ($file['name'] === $file_name) {
                    $this->logger->info("Found $file_name");
                    return $file;
                }
            }
        }
        return null;
    }

    /**
     * @param string $path
     * @param string $branch
     * @return string
     */
    public function getFileContents($path, $branch)
    {
        list($owner, $repo_name) = explode('/', $this->full_name);
        return $this->client->api('repo')->contents()->download($owner, $repo_name, $path, $branch);
    }

    /**
     * @param $from
     * @param $to
     * @return null|string
     */
    public function createBranch($from, $to)
    {
        list($owner, $repo_name) = explode('/', $this->full_name);

        // get the sha of $from to create a branch from
        $branches = $this->client->api('git')->references()->branches($owner, $repo_name);

        $to_branch = $this->findBranch($branches, $to);

        if (is_array($to_branch)) {
            // branch exists already
            return false;
        }

        $from_branch = $this->findBranch($branches, $from);

        if (is_array($from_branch)){
            $new_branch = $this->fullRefName($to);
            $this->client->api('git')->references()->create($owner, $repo_name, ['ref' => $new_branch, 'sha' => $from_branch['object']['sha']]);
            $this->logger->info(__METHOD__ . ' created ' . $new_branch);
            return true;
        } else {
            throw new BranchNotFoundException('Did not find branch "' . $from . '"');
        }
    }

    /**
     * @param array $branches
     * @param $name
     * @return array|null
     */
    private function findBranch(array $branches, $name)
    {
        $full_ref_name = $this->fullRefName($name);

        foreach ($branches as $branch) {
            $this->logger->info(__METHOD__ . ' ' . $branch['ref']);
            if ($branch['ref'] === $full_ref_name) {
                return $branch;
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return string
     */
    private function fullRefName($name)
    {
        return 'refs/heads/' . $name;
    }

    /**
     * @param string $path
     * @param string $sha
     * @param string $contents
     * @param string $to_branch
     */
    public function writeFile($path, $sha, $contents, $to_branch)
    {
        list($owner, $repo_name) = explode('/', $this->full_name);

        if (!is_null($sha)) {
            $this->client->api('repo')->contents()->update(
                $owner,
                $repo_name,
                trim($path, '/'),
                $contents,
                "Auto updates $path",
                $sha,
                $to_branch,
                [
                    'name' => $this->account_name,
                    'email' => $this->account_email
                ]
            );
        } else {
            $this->client->api('repo')->contents()->create(
                $owner,
                $repo_name,
                trim($path, '/'),
                $contents,
                "Auto updates $path",
                $to_branch,
                [
                    'name' => $this->account_name,
                    'email' => $this->account_email
                ]
            );
        }
    }

    /**
     * @param string $title
     * @param string $base target branch
     * @param string $head branch being pulled
     * @param string $body message
     */
    public function createPullRequest($title, $base, $head, $body)
    {
        list($owner, $repo_name) = explode('/', $this->full_name);

        $this->client->api('pr')->create(
            $owner,
            $repo_name,
            [
                'title' => $title,
                'base' => $base,
                'head' => $head,
                'body' => $body
            ]
        );
    }
}