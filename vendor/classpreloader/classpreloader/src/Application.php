<?php
namespace ClassPreloader;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Finder\Finder;
class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('Class Preloader', '1.2');
        $finder = new Finder();
        $finder->files()
            ->in(__DIR__ . '/Command')
            ->notName('Abstract*')
            ->name('*.php');
        foreach ($finder as $file) {
            $filename = str_replace('\\', '/', $file->getRealpath());
            $pos = strripos($filename, '/ClassPreloader/') + strlen('/ClassPreloader/src/');
            $class = __NAMESPACE__ . '\\'
                . substr(str_replace('/', '\\', substr($filename, $pos)), 0, -4);
            $this->add(new $class());
        }
    }
}
