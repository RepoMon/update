<?php namespace Ace\Update\Domain;

use Ace\Update\Domain\CommandLine;
use Ace\Update\Domain\ComposerConfig;
use Ace\Update\Domain\Repository;
use Ace\Update\Exception\FileNotFoundException;
use Ace\Update\Exception\InvalidFileContentsException;

/**
 * @package Ace\Update\Domain
 */
class ComposerDependencySet implements DependencySetInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var CommandLine
     */
    private $command_line;

    /**
     * @param Repository  $repository
     * @param CommandLine $command_line
     */
    public function __construct(Repository $repository, CommandLine $command_line)
    {
        $this->repository = $repository;
        $this->command_line = $command_line;
    }

    /**
     * @param $token
     */
    public function setGitHubToken($token)
    {
        $this->command_line->exec("composer config -g github-oauth.github.com $token");
    }

    /**
     * Update the composer config for the repository to use the parameter versions
     *
     * @param array $versions
     */
    public function setRequiredVersions(array $versions)
    {
        if (!$this->repository->hasFile('composer.json')){
            throw new FileNotFoundException("'composer.json not found'");
        }

        // create a composer object from the files in repository
        $composer_json = json_decode($this->repository->getFile('composer.json'), 1);

        if (!is_array($composer_json)){
            throw new InvalidFileContentsException(
                sprintf("'composer.json' is invalid: %s", $this->repository->getFile('composer.json'))
            );
        }

        $composer = new ComposerConfig($composer_json, []);

        foreach($versions as $library => $version) {
            $composer->setRequireVersion($library, $version);
        }

        // write the new composer config back to the file
        $this->repository->setFile(
            'composer.json',
            json_encode($composer->getComposerJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->repository->removeFile('composer.lock');

        // run composer install
        $this->command_line->exec('composer install  --prefer-dist --no-scripts');

        // Add composer.json and composer.lock to git branch
        $this->repository->add('composer.json');
        $this->repository->add('composer.lock');
    }

    /**
     * Update the currently configured dependencies
     */
    public function updateCurrent()
    {
        $path = $this->repository->findFilePath('composer.json');

        if (is_null($path)){
            throw new FileNotFoundException("'composer.json not found'");
        }

        $this->command_line->chDir($path);

        // update the current dependencies
        $this->command_line->exec('composer update  --prefer-dist --no-scripts');

        // Add composer.lock to git branch
        $this->repository->add("$path/composer.lock");
    }
}
