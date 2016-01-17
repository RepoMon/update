<?php namespace Ace\Update\Domain;

use Ace\Update\Exception\DirectoryNotFoundException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Represents a git repository
 */
class Repository
{
    /**
     * @var string url of remote git repo
     */
    private $url;

    /**
     * @var string location to clone remote repo into
     */
    private $directory;

    /**
     * @var string name of checkout
     */
    private $name;

    /**
     * @var string
     * Optional token to authenticate access to the remote repo
     */
    private $token;

    /**
     * @var Commandline
     */
    private $command_line;

    /**
     * @param $url string location of remote repo
     * @param $directory string directory location to clone repo into
     * @param null $token authentication token
     */
    public function __construct($url, $directory, $token = null)
    {
        $this->url = $url;
        $this->directory = $directory;
        $parts = explode('/', $this->url);
        $this->name = array_pop($parts);
        $this->token = $token;
        $this->command_line = new CommandLine($this->getCheckoutDirectory());
    }

    /**
     * @return DependencySetInterface
     */
    public function getDependencySet()
    {
        $command_line = new CommandLine($this->getCheckoutDirectory());

        /**
         * pass type of dependency set to use to constructor
         */
        $dependency_set = new ComposerDependencySet($this, $command_line);
        
        // assumes the token is for git hub
        if (!is_null($this->token)) {
            $dependency_set->setGitHubToken($this->token);
        }
        return $dependency_set;
    }

    /**
     * Return the director location of the checkout
     *
     * @return string
     */
    private function getCheckoutDirectory()
    {
        return $this->directory .'/' . $this->name;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Return an id string for this repo
     * @return string
     */
    public function getId()
    {
        return base64_encode($this->url);
    }

    /**
     * @return bool
     */
    public function isCheckedOut()
    {
        return is_dir($this->getCheckoutDirectory());
    }

    /**
     * Update the local repo from the remote
     * clones repo if it has not been checked out out yet
     * runs git remote update and git fetch --tags
     */
    public function update()
    {
        var_dump(__METHOD__ . ' ' . $this->directory);

        // cd to dir
        chdir($this->directory);

        // check if local repo exists
        if (!$this->isCheckedOut()) {
            var_dump('cloning ' . $this->generateUrl() . ' into ' . $this->directory);
            exec('git clone ' . $this->generateUrl(), $output, $return);
            var_dump($output);
            if (0 !== $return){
                throw new \Exception("Could not clone {$this->url}");
            }
        }

        $this->command_line->exec('git remote update');
        $this->command_line->exec('git fetch --tags origin');
    }

    /**
     * return a list of branch names for the local repo
     *
     * @return array
     */
    public function listLocalBranches()
    {
        try {
            $branches = $this->command_line->exec('git branch');

            return array_map(function ($name) {
                return trim($name, '* ');
            }, $branches);
        } catch (DirectoryNotFoundException $ex){
            return [];
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function isLocalBranch($name)
    {
        return in_array($name, $this->listLocalBranches());
    }

    /**
     * @param $name
     * @return bool
     */
    public function isBranch($name)
    {
        return in_array($name, $this->listAllBranches());
    }

    /**
     * @return array
     */
    public function listAllBranches()
    {
        try {
            $branches = $this->command_line->exec('git branch -a');

            $branches = array_map(function ($name) {
                return trim($name, '* ');
            }, $branches);

            $branches = array_map(function ($name) {
                return preg_replace('/^remotes\/origin\//', '', $name);
            }, $branches);

            // de-duplicate array and remove HEAD
            $branches = array_filter($branches, function ($name) {
                return !preg_match('/^HEAD/', $name);
            });

            return array_unique($branches);

        } catch (DirectoryNotFoundException $ex){
            return [];
        }
    }

    /**
     * Get the list of tags for the local repo
     * @return array
     */
    public function listTags()
    {
        return $this->command_line->exec('git tag -l');
    }

    /**
     * Return the lasted tag name according to semantic version format
     * Ignore tags with invalid semantic version names
     *
     * @return string
     */
    public function getLatestTag()
    {
        $versions = [];

        foreach ($this->listTags() as $tag) {
            $version = new SemanticVersion($tag);
            if ($version->isValid()){
                $versions []= $version;
            }
        }

        usort($versions, function(SemanticVersion $a, SemanticVersion $b) { return $a->compare($b);});
        return (string) array_pop($versions);
    }

    /**
     * Checkout the branch or tag with this name
     * @param $name
     */
    public function checkout($name)
    {
        $this->command_line->exec("git checkout $name");
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasFile($name)
    {
        return file_exists($this->getFilePath($name));
    }

    /**
     * @param $name
     * @return string the contents of file named $name in checkout
     */
    public function getFile($name)
    {
        if ($this->hasFile($name)) {
            return file_get_contents($this->getFilePath($name));
        }
    }

    /**
     * @param $name
     * @return string the contents of file named $name somewhere in checkout
     */
    public function findFile($name)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->directory . '/' . $this->name), RecursiveIteratorIterator::SELF_FIRST
        );

        foreach($files as $file){

            if ($name === $file->getFileName()){
                return file_get_contents($file->getPathName());
            }
        }

    }

    /**
     * @param $name string
     * @return string the path to the file with this name
     */
    public function findFilePath($name)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->directory . '/' . $this->name), RecursiveIteratorIterator::SELF_FIRST
        );

        foreach($files as $file){
            var_dump(__METHOD__ . ' ' . $file->getFileName());
            if ($name === $file->getFileName()){
                var_dump(__METHOD__ . ' found ' . $file->getPathInfo()->getRealPath());
                return $file->getPathInfo()->getRealPath();
            }
        }
    }

    /**
     * Write or overwrite file's contents
     * @param $name
     * @param $contents
     */
    public function setFile($name, $contents)
    {
        file_put_contents($this->getFilePath($name), $contents);
    }

    /**
     * @param $name string
     */
    public function removeFile($name)
    {
        $file = $this->getFilePath($name);

        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * Create a new branch
     * @param $name string
     * @param $from mixed
     */
    public function branch($name, $from = null)
    {
        $this->command_line->exec("git branch $name $from");
    }

    /**
     * Create a new tag
     * @param $name string
     * @param $comment string
     */
    public function tag($name, $comment)
    {

    }

    /**
     * Add a file
     * @param $name
     */
    public function add($name)
    {
        $file = $this->getFilePath($name);
        $this->command_line->exec('git add ' . $file);
    }

    /**
     * Commit added files
     */
    public function commit($msg)
    {
        $this->command_line->exec("git commit -m '$msg'");
    }

    /**
     * Returns the raw output of lines
     * It'd be more useful to return an array with each commit as a single element
     *
     * @return array
     */
    public function log()
    {
        return $this->command_line->exec('git log');
    }

    /**
     * Push commits to origin
     *
     * @return array
     */
    public function push($name = null)
    {
        return $this->command_line->exec('git push origin ' . $name);
    }

    /**
     * @return array
     */
    public function status($silent = true)
    {
        $cmd = 'git status';
        if ($silent) {
            $cmd .= ' -s';
        }

        return $this->command_line->exec($cmd);
    }

    /**
     * Insert the token into the url if it is set
     * @return string
     */
    private function generateUrl()
    {
        if (!is_null($this->token)){
            $parts = parse_url($this->url);
            // format is http://token@host/path for git hub at least
            $url = sprintf('%s://%s@%s%s', $parts['scheme'], $this->token, $parts['host'], $parts['path']);
            return $url;
        }

        return $this->url;
    }

    /**
     * @param $name string
     * @return string
     */
    private function getFilePath($name)
    {
        return $this->directory . '/' . $this->name . '/' . $name;
    }
}
