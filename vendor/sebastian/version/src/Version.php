<?php
namespace SebastianBergmann;
class Version
{
    private $path;
    private $release;
    private $version;
    public function __construct($release, $path)
    {
        $this->release = $release;
        $this->path    = $path;
    }
    public function getVersion()
    {
        if ($this->version === null) {
            if (count(explode('.', $this->release)) == 3) {
                $this->version = $this->release;
            } else {
                $this->version = $this->release . '-dev';
            }
            $git = $this->getGitInformation($this->path);
            if ($git) {
                if (count(explode('.', $this->release)) == 3) {
                    $this->version = $git;
                } else {
                    $git = explode('-', $git);
                    $this->version = $this->release . '-' . end($git);
                }
            }
        }
        return $this->version;
    }
    private function getGitInformation($path)
    {
        if (!is_dir($path . DIRECTORY_SEPARATOR . '.git')) {
            return false;
        }
        $dir = getcwd();
        chdir($path);
        $returnCode = 1;
        $result = @exec('git describe --tags 2>&1', $output, $returnCode);
        chdir($dir);
        if ($returnCode !== 0) {
            return false;
        }
        return $result;
    }
}
