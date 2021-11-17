<?php
namespace PhpSpec\Locator\PSR0;
use PhpSpec\Locator\ResourceInterface;
use PhpSpec\Locator\ResourceLocatorInterface;
use PhpSpec\Util\Filesystem;
use InvalidArgumentException;
class PSR0Locator implements ResourceLocatorInterface
{
    private $srcPath;
    private $specPath;
    private $srcNamespace;
    private $specNamespace;
    private $fullSrcPath;
    private $fullSpecPath;
    private $filesystem;
    private $psr4Prefix;
    public function __construct(
        $srcNamespace = '',
        $specNamespacePrefix = 'spec',
        $srcPath = 'src',
        $specPath = '.',
        Filesystem $filesystem = null,
        $psr4Prefix = null
    ) {
        $this->filesystem = $filesystem ?: new Filesystem();
        $sepr = DIRECTORY_SEPARATOR;
        $this->srcPath       = rtrim(realpath($srcPath), '/\\').$sepr;
        $this->specPath      = rtrim(realpath($specPath), '/\\').$sepr;
        $this->srcNamespace  = ltrim(trim($srcNamespace, ' \\').'\\', '\\');
        $this->psr4Prefix    = (null === $psr4Prefix) ? null : ltrim(trim($psr4Prefix, ' \\').'\\', '\\');
        if (null !== $this->psr4Prefix  && substr($this->srcNamespace, 0, strlen($psr4Prefix)) !== $psr4Prefix) {
            throw new InvalidArgumentException('PSR4 prefix doesn\'t match given class namespace.'.PHP_EOL);
        }
        $srcNamespacePath = null === $this->psr4Prefix ?
            $this->srcNamespace :
            substr($this->srcNamespace, strlen($this->psr4Prefix));
        $this->specNamespace = trim($specNamespacePrefix, ' \\').'\\'.$this->srcNamespace;
        $specNamespacePath = trim($specNamespacePrefix, ' \\').'\\'.$srcNamespacePath;
        $this->fullSrcPath   = $this->srcPath.str_replace('\\', $sepr, $srcNamespacePath);
        $this->fullSpecPath  = $this->specPath.str_replace('\\', $sepr, $specNamespacePath);
        if ($sepr === $this->srcPath) {
            throw new InvalidArgumentException(sprintf(
                'Source code path should be existing filesystem path, but "%s" given.',
                $srcPath
            ));
        }
        if ($sepr === $this->specPath) {
            throw new InvalidArgumentException(sprintf(
                'Specs code path should be existing filesystem path, but "%s" given.',
                $specPath
            ));
        }
    }
    public function getFullSrcPath()
    {
        return $this->fullSrcPath;
    }
    public function getFullSpecPath()
    {
        return $this->fullSpecPath;
    }
    public function getSrcNamespace()
    {
        return $this->srcNamespace;
    }
    public function getSpecNamespace()
    {
        return $this->specNamespace;
    }
    public function getAllResources()
    {
        return $this->findSpecResources($this->fullSpecPath);
    }
    public function supportsQuery($query)
    {
        $sepr = DIRECTORY_SEPARATOR;
        $path = rtrim(realpath(str_replace(array('\\', '/'), $sepr, $query)), $sepr);
        if (null === $path) {
            return false;
        }
        return 0 === strpos($path, $this->srcPath)
            || 0 === strpos($path, $this->specPath)
        ;
    }
    public function findResources($query)
    {
        $sepr = DIRECTORY_SEPARATOR;
        $path = rtrim(realpath(str_replace(array('\\', '/'), $sepr, $query)), $sepr);
        if ('.php' !== substr($path, -4)) {
            $path .= $sepr;
        }
        if ($path && 0 === strpos($path, $this->fullSrcPath)) {
            $path = $this->fullSpecPath.substr($path, strlen($this->fullSrcPath));
            $path = preg_replace('/\.php/', 'Spec.php', $path);
            return $this->findSpecResources($path);
        }
        if ($path && 0 === strpos($path, $this->srcPath)) {
            $path = $this->fullSpecPath.substr($path, strlen($this->srcPath));
            $path = preg_replace('/\.php/', 'Spec.php', $path);
            return $this->findSpecResources($path);
        }
        if ($path && 0 === strpos($path, $this->specPath)) {
            return $this->findSpecResources($path);
        }
        return array();
    }
    public function supportsClass($classname)
    {
        $classname = str_replace('/', '\\', $classname);
        return '' === $this->srcNamespace
            || 0  === strpos($classname, $this->srcNamespace)
            || 0  === strpos($classname, $this->specNamespace)
        ;
    }
    public function createResource($classname)
    {
        $this->validatePsr0Classname($classname);
        $classname = str_replace('/', '\\', $classname);
        if (0 === strpos($classname, $this->specNamespace)) {
            $relative = substr($classname, strlen($this->specNamespace));
            return new PSR0Resource(explode('\\', $relative), $this);
        }
        if ('' === $this->srcNamespace || 0 === strpos($classname, $this->srcNamespace)) {
            $relative = substr($classname, strlen($this->srcNamespace));
            return new PSR0Resource(explode('\\', $relative), $this);
        }
        return null;
    }
    public function getPriority()
    {
        return 0;
    }
    protected function findSpecResources($path)
    {
        if (!$this->filesystem->pathExists($path)) {
            return array();
        }
        if ('.php' === substr($path, -4)) {
            return array($this->createResourceFromSpecFile(realpath($path)));
        }
        $resources = array();
        foreach ($this->filesystem->findSpecFilesIn($path) as $file) {
            $resources[] = $this->createResourceFromSpecFile($file->getRealPath());
        }
        return $resources;
    }
    private function findSpecClassname($path)
    {
        $namespace = '';
        $content   = $this->filesystem->getFileContents($path);
        $tokens    = token_get_all($content);
        $count     = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j][0] === T_STRING) {
                        $namespace .= $tokens[$j][1].'\\';
                    } elseif ($tokens[$j] === '{' || $tokens[$j] === ';') {
                        break;
                    }
                }
            }
            if ($tokens[$i][0] === T_CLASS) {
                for ($j = $i+1; $j < $count; $j++) {
                    if ($tokens[$j] === '{') {
                        return $namespace.$tokens[$i+2][1];
                    }
                }
            }
        }
        return null;
    }
    private function createResourceFromSpecFile($path)
    {
        $classname = $this->findSpecClassname($path);
        if (null === $classname) {
            throw new \RuntimeException('Spec file does not contains any class definition.');
        }
        $specNamespace = trim($this->getSpecNamespace(), '\\').'\\';
        if (0 !== strpos($classname, $specNamespace)) {
            throw new \RuntimeException(sprintf(
                'Spec class `%s` must be in the base spec namespace `%s`.',
                $classname,
                $this->getSpecNamespace()
            ));
        }
        $classname = substr($classname, strlen($specNamespace));
        $classname = preg_replace('/Spec$/', '', $classname);
        return new PSR0Resource(explode('\\', $classname), $this);
    }
    private function validatePsr0Classname($classname)
    {
        $pattern = '/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*[\/\\\\]?)*[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';
        if (!preg_match($pattern, $classname)) {
            throw new InvalidArgumentException(
                sprintf('String "%s" is not a valid class name.', $classname).PHP_EOL.
                'Please see reference document: '.
                'https:
            );
        }
    }
}
