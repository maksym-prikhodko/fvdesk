<?php
namespace Psy;
use Psy\Exception\RuntimeException;
use Psy\ExecutionLoop\ForkingLoop;
use Psy\ExecutionLoop\Loop;
use Psy\Output\OutputPager;
use Psy\Output\ShellOutput;
use Psy\Presenter\PresenterManager;
use Psy\Readline\GNUReadline;
use Psy\Readline\Libedit;
use Psy\Readline\Readline;
use Psy\Readline\Transient;
use Psy\TabCompletion\AutoCompleter;
use XdgBaseDir\Xdg;
class Configuration
{
    private static $AVAILABLE_OPTIONS = array(
        'defaultIncludes', 'useReadline', 'usePcntl', 'codeCleaner', 'pager',
        'loop', 'configDir', 'dataDir', 'runtimeDir', 'manualDbFile',
        'requireSemicolons', 'historySize', 'eraseDuplicates', 'tabCompletion',
    );
    private $defaultIncludes;
    private $configDir;
    private $dataDir;
    private $runtimeDir;
    private $configFile;
    private $historyFile;
    private $historySize;
    private $eraseDuplicates;
    private $manualDbFile;
    private $hasReadline;
    private $useReadline;
    private $hasPcntl;
    private $usePcntl;
    private $newCommands = array();
    private $requireSemicolons = false;
    private $tabCompletion;
    private $tabCompletionMatchers = array();
    private $readline;
    private $output;
    private $shell;
    private $cleaner;
    private $pager;
    private $loop;
    private $manualDb;
    private $presenters;
    private $completer;
    public function __construct(array $config = array())
    {
        if (isset($config['configFile'])) {
            $this->configFile = $config['configFile'];
        } elseif ($configFile = getenv('PSYSH_CONFIG')) {
            $this->configFile = $configFile;
        }
        if (isset($config['baseDir'])) {
            $msg = "The 'baseDir' configuration option is deprecated. " .
                "Please specify 'configDir' and 'dataDir' options instead.";
            trigger_error($msg, E_USER_DEPRECATED);
            $this->setConfigDir($config['baseDir']);
            $this->setDataDir($config['baseDir']);
        }
        unset($config['configFile'], $config['baseDir']);
        $this->loadConfig($config);
        $this->init();
    }
    public function init()
    {
        $this->hasReadline = function_exists('readline');
        $this->hasPcntl    = function_exists('pcntl_signal') && function_exists('posix_getpid');
        if ($configFile = $this->getConfigFile()) {
            $this->loadConfigFile($configFile);
        }
    }
    public function getConfigFile()
    {
        if (isset($this->configFile)) {
            return $this->configFile;
        }
        foreach ($this->getConfigDirs() as $dir) {
            $file = $dir . '/config.php';
            if (@is_file($file)) {
                return $this->configFile = $file;
            }
            $file = $dir . '/rc.php';
            if (@is_file($file)) {
                return $this->configFile = $file;
            }
        }
    }
    private function getPsyHome()
    {
        if ($home = getenv('HOME')) {
            return $home . '/.psysh';
        }
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $oldHome = strtr(getenv('HOMEDRIVE') . '/' . getenv('HOMEPATH') . '/.psysh', '\\', '/');
            if ($appData = getenv('APPDATA')) {
                $home = strtr($appData, '\\', '/') . '/PsySH';
                if (is_dir($oldHome) && !is_dir($home)) {
                    $msg = sprintf(
                        "Config directory found at '%s'. Please move it to '%s'.",
                        strtr($oldHome, '/', '\\'),
                        strtr($home, '/', '\\')
                    );
                    trigger_error($msg, E_USER_DEPRECATED);
                    return $oldHome;
                }
                return $home;
            }
        }
    }
    protected function getConfigDirs()
    {
        if (isset($this->configDir)) {
            return array($this->configDir);
        }
        $xdg = new Xdg();
        $dirs = array_map(function ($dir) {
            return $dir . '/psysh';
        }, $xdg->getConfigDirs());
        if ($home = $this->getPsyHome()) {
            array_unshift($dirs, $home);
        }
        return $dirs;
    }
    protected function getDataDirs()
    {
        if (isset($this->dataDir)) {
            return array($this->dataDir);
        }
        $xdg = new Xdg();
        $dirs = array_map(function ($dir) {
            return $dir . '/psysh';
        }, $xdg->getDataDirs());
        if ($home = $this->getPsyHome()) {
            array_unshift($dirs, $home);
        }
        return $dirs;
    }
    public function loadConfig(array $options)
    {
        foreach (self::$AVAILABLE_OPTIONS as $option) {
            if (isset($options[$option])) {
                $method = 'set' . ucfirst($option);
                $this->$method($options[$option]);
            }
        }
        foreach (array('commands', 'tabCompletionMatchers', 'presenters') as $option) {
            if (isset($options[$option])) {
                $method = 'add' . ucfirst($option);
                $this->$method($options[$option]);
            }
        }
    }
    public function loadConfigFile($file)
    {
        $__psysh_config_file__ = $file;
        $load = function ($config) use ($__psysh_config_file__) {
            $result = require $__psysh_config_file__;
            if ($result !== 1) {
                return $result;
            }
        };
        $result = $load($this);
        if (!empty($result)) {
            if (is_array($result)) {
                $this->loadConfig($result);
            } else {
                throw new \InvalidArgumentException('Psy Shell configuration must return an array of options');
            }
        }
    }
    public function setDefaultIncludes(array $includes = array())
    {
        $this->defaultIncludes = $includes;
    }
    public function getDefaultIncludes()
    {
        return $this->defaultIncludes ?: array();
    }
    public function setConfigDir($dir)
    {
        $this->configDir = (string) $dir;
    }
    public function getConfigDir()
    {
        return $this->configDir;
    }
    public function setDataDir($dir)
    {
        $this->dataDir = (string) $dir;
    }
    public function getDataDir()
    {
        return $this->dataDir;
    }
    public function setRuntimeDir($dir)
    {
        $this->runtimeDir = (string) $dir;
    }
    public function getRuntimeDir()
    {
        if (!isset($this->runtimeDir)) {
            $xdg = new Xdg();
            $this->runtimeDir = $xdg->getRuntimeDir() . '/psysh';
        }
        if (!is_dir($this->runtimeDir)) {
            mkdir($this->runtimeDir, 0700, true);
        }
        return $this->runtimeDir;
    }
    public function setTempDir($dir)
    {
        trigger_error("'setTempDir' is deprecated. Use 'setRuntimeDir' instead.", E_USER_DEPRECATED);
        return $this->setRuntimeDir($dir);
    }
    public function getTempDir()
    {
        trigger_error("'getTempDir' is deprecated. Use 'getRuntimeDir' instead.", E_USER_DEPRECATED);
        return $this->getRuntimeDir();
    }
    public function setHistoryFile($file)
    {
        $this->historyFile = (string) $file;
    }
    public function getHistoryFile()
    {
        if (isset($this->historyFile)) {
            return $this->historyFile;
        }
        foreach ($this->getConfigDirs() as $dir) {
            $file = $dir . '/psysh_history';
            if (@is_file($file)) {
                return $this->historyFile = $file;
            }
            $file = $dir . '/history';
            if (@is_file($file)) {
                return $this->historyFile = $file;
            }
        }
        if (isset($this->configDir)) {
            $dir = $this->configDir;
        } else {
            $xdg = new Xdg();
            $dir = $xdg->getHomeConfigDir();
        }
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $file = $dir . '/psysh_history';
        return $this->historyFile = $file;
    }
    public function setHistorySize($value)
    {
        $this->historySize = (int) $value;
    }
    public function getHistorySize()
    {
        return $this->historySize;
    }
    public function setEraseDuplicates($value)
    {
        $this->eraseDuplicates = (bool) $value;
    }
    public function getEraseDuplicates()
    {
        return $this->eraseDuplicates;
    }
    public function getTempFile($type, $pid)
    {
        return tempnam($this->getRuntimeDir(), $type . '_' . $pid . '_');
    }
    public function getPipe($type, $pid)
    {
        return sprintf('%s/%s_%s', $this->getRuntimeDir(), $type, $pid);
    }
    public function hasReadline()
    {
        return $this->hasReadline;
    }
    public function setUseReadline($useReadline)
    {
        $this->useReadline = (bool) $useReadline;
    }
    public function useReadline()
    {
        return isset($this->useReadline) ? ($this->hasReadline && $this->useReadline) : $this->hasReadline;
    }
    public function setReadline(Readline $readline)
    {
        $this->readline = $readline;
    }
    public function getReadline()
    {
        if (!isset($this->readline)) {
            $className = $this->getReadlineClass();
            $this->readline = new $className(
                $this->getHistoryFile(),
                $this->getHistorySize(),
                $this->getEraseDuplicates()
            );
        }
        return $this->readline;
    }
    private function getReadlineClass()
    {
        if ($this->useReadline()) {
            if (GNUReadline::isSupported()) {
                return 'Psy\Readline\GNUReadline';
            } elseif (Libedit::isSupported()) {
                return 'Psy\Readline\Libedit';
            }
        }
        return 'Psy\Readline\Transient';
    }
    public function hasPcntl()
    {
        return $this->hasPcntl;
    }
    public function setUsePcntl($usePcntl)
    {
        $this->usePcntl = (bool) $usePcntl;
    }
    public function usePcntl()
    {
        return isset($this->usePcntl) ? ($this->hasPcntl && $this->usePcntl) : $this->hasPcntl;
    }
    public function setRequireSemicolons($requireSemicolons)
    {
        $this->requireSemicolons = (bool) $requireSemicolons;
    }
    public function requireSemicolons()
    {
        return $this->requireSemicolons;
    }
    public function setCodeCleaner(CodeCleaner $cleaner)
    {
        $this->cleaner = $cleaner;
    }
    public function getCodeCleaner()
    {
        if (!isset($this->cleaner)) {
            $this->cleaner = new CodeCleaner();
        }
        return $this->cleaner;
    }
    public function setTabCompletion($tabCompletion)
    {
        $this->tabCompletion = (bool) $tabCompletion;
    }
    public function getTabCompletion()
    {
        return isset($this->tabCompletion) ? ($this->hasReadline && $this->tabCompletion) : $this->hasReadline;
    }
    public function setOutput(ShellOutput $output)
    {
        $this->output = $output;
    }
    public function getOutput()
    {
        if (!isset($this->output)) {
            $this->output = new ShellOutput(ShellOutput::VERBOSITY_NORMAL, null, null, $this->getPager());
        }
        return $this->output;
    }
    public function setPager($pager)
    {
        if ($pager && !is_string($pager) && !$pager instanceof OutputPager) {
            throw new \InvalidArgumentException('Unexpected pager instance.');
        }
        $this->pager = $pager;
    }
    public function getPager()
    {
        if (!isset($this->pager) && $this->usePcntl()) {
            if ($pager = ini_get('cli.pager')) {
                $this->pager = $pager;
            } elseif ($less = exec('which less 2>/dev/null')) {
                $this->pager = $less . ' -R -S -F -X';
            }
        }
        return $this->pager;
    }
    public function setLoop(Loop $loop)
    {
        $this->loop = $loop;
    }
    public function getLoop()
    {
        if (!isset($this->loop)) {
            if ($this->usePcntl()) {
                $this->loop = new ForkingLoop($this);
            } else {
                $this->loop = new Loop($this);
            }
        }
        return $this->loop;
    }
    public function setAutoCompleter(AutoCompleter $completer)
    {
        $this->completer = $completer;
    }
    public function getAutoCompleter()
    {
        if (!isset($this->completer)) {
            $this->completer = new AutoCompleter();
        }
        return $this->completer;
    }
    public function getTabCompletionMatchers()
    {
        return $this->tabCompletionMatchers;
    }
    public function addTabCompletionMatchers(array $matchers)
    {
        $this->tabCompletionMatchers = array_merge($this->tabCompletionMatchers, $matchers);
        if (isset($this->shell)) {
            $this->shell->addTabCompletionMatchers($this->tabCompletionMatchers);
        }
    }
    public function addCommands(array $commands)
    {
        $this->newCommands = array_merge($this->newCommands, $commands);
        if (isset($this->shell)) {
            $this->doAddCommands();
        }
    }
    private function doAddCommands()
    {
        if (!empty($this->newCommands)) {
            $this->shell->addCommands($this->newCommands);
            $this->newCommands = array();
        }
    }
    public function setShell(Shell $shell)
    {
        $this->shell = $shell;
        $this->doAddCommands();
    }
    public function setManualDbFile($filename)
    {
        $this->manualDbFile = (string) $filename;
    }
    public function getManualDbFile()
    {
        if (isset($this->manualDbFile)) {
            return $this->manualDbFile;
        }
        foreach ($this->getDataDirs() as $dir) {
            $file = $dir . '/php_manual.sqlite';
            if (@is_file($file)) {
                return $this->manualDbFile = $file;
            }
        }
    }
    public function getManualDb()
    {
        if (!isset($this->manualDb)) {
            $dbFile = $this->getManualDbFile();
            if (is_file($dbFile)) {
                try {
                    $this->manualDb = new \PDO('sqlite:' . $dbFile);
                } catch (\PDOException $e) {
                    if ($e->getMessage() === 'could not find driver') {
                        throw new RuntimeException('SQLite PDO driver not found', 0, $e);
                    } else {
                        throw $e;
                    }
                }
            }
        }
        return $this->manualDb;
    }
    public function addPresenters(array $presenters)
    {
        $manager = $this->getPresenterManager();
        foreach ($presenters as $presenter) {
            $manager->addPresenter($presenter);
        }
    }
    public function getPresenterManager()
    {
        if (!isset($this->presenters)) {
            $this->presenters = new PresenterManager();
        }
        return $this->presenters;
    }
}
