<?php
use SebastianBergmann\Environment\Runtime;
class PHP_CodeCoverage
{
    private $driver;
    private $filter;
    private $cacheTokens = false;
    private $checkForUnintentionallyCoveredCode = false;
    private $forceCoversAnnotation = false;
    private $mapTestClassNameToCoveredClassName = false;
    private $addUncoveredFilesFromWhitelist = true;
    private $processUncoveredFilesFromWhitelist = false;
    private $currentId;
    private $data = array();
    private $ignoredLines = array();
    private $tests = array();
    public function __construct(PHP_CodeCoverage_Driver $driver = null, PHP_CodeCoverage_Filter $filter = null)
    {
        if ($driver === null) {
            $runtime = new Runtime;
            if ($runtime->isHHVM()) {
                $driver = new PHP_CodeCoverage_Driver_HHVM;
            } elseif ($runtime->hasXdebug()) {
                $driver = new PHP_CodeCoverage_Driver_Xdebug;
            } else {
                throw new PHP_CodeCoverage_Exception('No code coverage driver available');
            }
        }
        if ($filter === null) {
            $filter = new PHP_CodeCoverage_Filter;
        }
        $this->driver = $driver;
        $this->filter = $filter;
    }
    public function getReport()
    {
        $factory = new PHP_CodeCoverage_Report_Factory;
        return $factory->create($this);
    }
    public function clear()
    {
        $this->currentId = null;
        $this->data      = array();
        $this->tests     = array();
    }
    public function filter()
    {
        return $this->filter;
    }
    public function getData($raw = false)
    {
        if (!$raw && $this->addUncoveredFilesFromWhitelist) {
            $this->addUncoveredFilesFromWhitelist();
        }
        if (!$raw && !$this->filter->hasWhitelist()) {
            $this->applyListsFilter($this->data);
        }
        return $this->data;
    }
    public function setData(array $data)
    {
        $this->data = $data;
    }
    public function getTests()
    {
        return $this->tests;
    }
    public function setTests(array $tests)
    {
        $this->tests = $tests;
    }
    public function start($id, $clear = false)
    {
        if (!is_bool($clear)) {
            throw PHP_CodeCoverage_Util_InvalidArgumentHelper::factory(
                1,
                'boolean'
            );
        }
        if ($clear) {
            $this->clear();
        }
        $this->currentId = $id;
        $this->driver->start();
    }
    public function stop($append = true, $linesToBeCovered = array(), array $linesToBeUsed = array())
    {
        if (!is_bool($append)) {
            throw PHP_CodeCoverage_Util_InvalidArgumentHelper::factory(
                1,
                'boolean'
            );
        }
        if (!is_array($linesToBeCovered) && $linesToBeCovered !== false) {
            throw PHP_CodeCoverage_Util_InvalidArgumentHelper::factory(
                2,
                'array or false'
            );
        }
        $data = $this->driver->stop();
        $this->append($data, null, $append, $linesToBeCovered, $linesToBeUsed);
        $this->currentId = null;
        return $data;
    }
    public function append(array $data, $id = null, $append = true, $linesToBeCovered = array(), array $linesToBeUsed = array())
    {
        if ($id === null) {
            $id = $this->currentId;
        }
        if ($id === null) {
            throw new PHP_CodeCoverage_Exception;
        }
        $this->applyListsFilter($data);
        $this->applyIgnoredLinesFilter($data);
        $this->initializeFilesThatAreSeenTheFirstTime($data);
        if (!$append) {
            return;
        }
        if ($id != 'UNCOVERED_FILES_FROM_WHITELIST') {
            $this->applyCoversAnnotationFilter(
                $data,
                $linesToBeCovered,
                $linesToBeUsed
            );
        }
        if (empty($data)) {
            return;
        }
        $status = null;
        if ($id instanceof PHPUnit_Framework_TestCase) {
            $status = $id->getStatus();
            $id     = get_class($id) . '::' . $id->getName();
        } elseif ($id instanceof PHPUnit_Extensions_PhptTestCase) {
            $id = $id->getName();
        }
        $this->tests[$id] = $status;
        foreach ($data as $file => $lines) {
            if (!$this->filter->isFile($file)) {
                continue;
            }
            foreach ($lines as $k => $v) {
                if ($v == 1) {
                    $this->data[$file][$k][] = $id;
                }
            }
        }
    }
    public function merge(PHP_CodeCoverage $that)
    {
        foreach ($that->getData() as $file => $lines) {
            if (!isset($this->data[$file])) {
                if (!$that->filter()->isFiltered($file)) {
                    $this->data[$file] = $lines;
                }
                continue;
            }
            foreach ($lines as $line => $data) {
                if ($data !== null) {
                    if (!isset($this->data[$file][$line])) {
                        $this->data[$file][$line] = $data;
                    } else {
                        $this->data[$file][$line] = array_unique(
                            array_merge($this->data[$file][$line], $data)
                        );
                    }
                }
            }
        }
        $this->tests = array_merge($this->tests, $that->getTests());
    }
    public function setCacheTokens($flag)
    {
        if (!is_bool($flag)) {
            throw PHP_CodeCoverage_Util_InvalidArgumentHelper::factory(
                1,
                'boolean'
            );
        }
        $this->cacheTokens = $flag;
    }
    public function getCacheTokens()
    {
        return $this->cacheTokens;
    }
    public function setCheckForUnintentionallyCoveredCode($flag)
    {
        if (!is_bool($flag)) {
            throw PHP_CodeCoverage_Util_InvalidArgumentHelper::factory(
                1,
                'boolean'
            );
        }
        $this->checkForUnintentionallyCoveredCode = $flag;
    }
    public function setForceCoversAnnotation($flag)
    {
        if (!is_bool($flag)) {
            throw PHP_CodeCoverage_Util_InvalidArgumentHelper::factory(
                1,
                'boolean'
            );
        }
        $this->forceCoversAnnotation = $flag;
    }
    public function setMapTestClassNameToCoveredClassName($flag)
    {
        if (!is_bool($flag)) {
            throw PHP_CodeCoverage_Util_InvalidArgumentHelper::factory(
                1,
                'boolean'
            );
        }
        $this->mapTestClassNameToCoveredClassName = $flag;
    }
    public function setAddUncoveredFilesFromWhitelist($flag)
    {
        if (!is_bool($flag)) {
            throw PHP_CodeCoverage_Util_InvalidArgumentHelper::factory(
                1,
                'boolean'
            );
        }
        $this->addUncoveredFilesFromWhitelist = $flag;
    }
    public function setProcessUncoveredFilesFromWhitelist($flag)
    {
        if (!is_bool($flag)) {
            throw PHP_CodeCoverage_Util_InvalidArgumentHelper::factory(
                1,
                'boolean'
            );
        }
        $this->processUncoveredFilesFromWhitelist = $flag;
    }
    private function applyCoversAnnotationFilter(array &$data, $linesToBeCovered, array $linesToBeUsed)
    {
        if ($linesToBeCovered === false ||
            ($this->forceCoversAnnotation && empty($linesToBeCovered))) {
            $data = array();
            return;
        }
        if (empty($linesToBeCovered)) {
            return;
        }
        if ($this->checkForUnintentionallyCoveredCode) {
            $this->performUnintentionallyCoveredCodeCheck(
                $data,
                $linesToBeCovered,
                $linesToBeUsed
            );
        }
        $data = array_intersect_key($data, $linesToBeCovered);
        foreach (array_keys($data) as $filename) {
            $_linesToBeCovered = array_flip($linesToBeCovered[$filename]);
            $data[$filename] = array_intersect_key(
                $data[$filename],
                $_linesToBeCovered
            );
        }
    }
    private function applyListsFilter(array &$data)
    {
        foreach (array_keys($data) as $filename) {
            if ($this->filter->isFiltered($filename)) {
                unset($data[$filename]);
            }
        }
    }
    private function applyIgnoredLinesFilter(array &$data)
    {
        foreach (array_keys($data) as $filename) {
            if (!$this->filter->isFile($filename)) {
                continue;
            }
            foreach ($this->getLinesToBeIgnored($filename) as $line) {
                unset($data[$filename][$line]);
            }
        }
    }
    private function initializeFilesThatAreSeenTheFirstTime(array $data)
    {
        foreach ($data as $file => $lines) {
            if ($this->filter->isFile($file) && !isset($this->data[$file])) {
                $this->data[$file] = array();
                foreach ($lines as $k => $v) {
                    $this->data[$file][$k] = $v == -2 ? null : array();
                }
            }
        }
    }
    private function addUncoveredFilesFromWhitelist()
    {
        $data           = array();
        $uncoveredFiles = array_diff(
            $this->filter->getWhitelist(),
            array_keys($this->data)
        );
        foreach ($uncoveredFiles as $uncoveredFile) {
            if (!file_exists($uncoveredFile)) {
                continue;
            }
            if ($this->processUncoveredFilesFromWhitelist) {
                $this->processUncoveredFileFromWhitelist(
                    $uncoveredFile,
                    $data,
                    $uncoveredFiles
                );
            } else {
                $data[$uncoveredFile] = array();
                $lines = count(file($uncoveredFile));
                for ($i = 1; $i <= $lines; $i++) {
                    $data[$uncoveredFile][$i] = -1;
                }
            }
        }
        $this->append($data, 'UNCOVERED_FILES_FROM_WHITELIST');
    }
    private function processUncoveredFileFromWhitelist($uncoveredFile, array &$data, array $uncoveredFiles)
    {
        $this->driver->start();
        include_once $uncoveredFile;
        $coverage = $this->driver->stop();
        foreach ($coverage as $file => $fileCoverage) {
            if (!isset($data[$file]) &&
                in_array($file, $uncoveredFiles)) {
                foreach (array_keys($fileCoverage) as $key) {
                    if ($fileCoverage[$key] == 1) {
                        $fileCoverage[$key] = -1;
                    }
                }
                $data[$file] = $fileCoverage;
            }
        }
    }
    private function getLinesToBeIgnored($filename)
    {
        if (!is_string($filename)) {
            throw PHP_CodeCoverage_Util_InvalidArgumentHelper::factory(
                1,
                'string'
            );
        }
        if (!isset($this->ignoredLines[$filename])) {
            $this->ignoredLines[$filename] = array();
            $ignore                        = false;
            $stop                          = false;
            $lines                         = file($filename);
            $numLines                      = count($lines);
            foreach ($lines as $index => $line) {
                if (!trim($line)) {
                    $this->ignoredLines[$filename][] = $index + 1;
                }
            }
            if ($this->cacheTokens) {
                $tokens = PHP_Token_Stream_CachingFactory::get($filename);
            } else {
                $tokens = new PHP_Token_Stream($filename);
            }
            $classes = array_merge($tokens->getClasses(), $tokens->getTraits());
            $tokens  = $tokens->tokens();
            foreach ($tokens as $token) {
                switch (get_class($token)) {
                    case 'PHP_Token_COMMENT':
                    case 'PHP_Token_DOC_COMMENT':
                        $_token = trim($token);
                        $_line  = trim($lines[$token->getLine() - 1]);
                        if ($_token == '
                            $_token == '
                            $ignore = true;
                            $stop   = true;
                        } elseif ($_token == '
                            $_token == '
                            $ignore = true;
                        } elseif ($_token == '
                            $_token == '
                            $stop = true;
                        }
                        if (!$ignore) {
                            $start = $token->getLine();
                            $end = $start + substr_count($token, "\n");
                            if (0 !== strpos($_token, $_line)) {
                                $start++;
                            }
                            for ($i = $start; $i < $end; $i++) {
                                $this->ignoredLines[$filename][] = $i;
                            }
                            if (0 === strpos($_token, '' === substr(trim($lines[$i-1]), -2)) {
                                $this->ignoredLines[$filename][] = $i;
                            }
                        }
                        break;
                    case 'PHP_Token_INTERFACE':
                    case 'PHP_Token_TRAIT':
                    case 'PHP_Token_CLASS':
                    case 'PHP_Token_FUNCTION':
                        $docblock = $token->getDocblock();
                        $this->ignoredLines[$filename][] = $token->getLine();
                        if (strpos($docblock, '@codeCoverageIgnore')) {
                            $endLine = $token->getEndLine();
                            for ($i = $token->getLine(); $i <= $endLine; $i++) {
                                $this->ignoredLines[$filename][] = $i;
                            }
                        } elseif ($token instanceof PHP_Token_INTERFACE ||
                            $token instanceof PHP_Token_TRAIT ||
                            $token instanceof PHP_Token_CLASS) {
                            if (empty($classes[$token->getName()]['methods'])) {
                                for ($i = $token->getLine();
                                     $i <= $token->getEndLine();
                                     $i++) {
                                    $this->ignoredLines[$filename][] = $i;
                                }
                            } else {
                                $firstMethod = array_shift(
                                    $classes[$token->getName()]['methods']
                                );
                                do {
                                    $lastMethod = array_pop(
                                        $classes[$token->getName()]['methods']
                                    );
                                } while ($lastMethod !== null &&
                                    substr($lastMethod['signature'], 0, 18) == 'anonymous function');
                                if ($lastMethod === null) {
                                    $lastMethod = $firstMethod;
                                }
                                for ($i = $token->getLine();
                                     $i < $firstMethod['startLine'];
                                     $i++) {
                                    $this->ignoredLines[$filename][] = $i;
                                }
                                for ($i = $token->getEndLine();
                                     $i > $lastMethod['endLine'];
                                     $i--) {
                                    $this->ignoredLines[$filename][] = $i;
                                }
                            }
                        }
                        break;
                    case 'PHP_Token_NAMESPACE':
                        $this->ignoredLines[$filename][] = $token->getEndLine();
                    case 'PHP_Token_OPEN_TAG':
                    case 'PHP_Token_CLOSE_TAG':
                    case 'PHP_Token_USE':
                        $this->ignoredLines[$filename][] = $token->getLine();
                        break;
                }
                if ($ignore) {
                    $this->ignoredLines[$filename][] = $token->getLine();
                    if ($stop) {
                        $ignore = false;
                        $stop   = false;
                    }
                }
            }
            $this->ignoredLines[$filename][] = $numLines + 1;
            $this->ignoredLines[$filename] = array_unique(
                $this->ignoredLines[$filename]
            );
            sort($this->ignoredLines[$filename]);
        }
        return $this->ignoredLines[$filename];
    }
    private function performUnintentionallyCoveredCodeCheck(array &$data, array $linesToBeCovered, array $linesToBeUsed)
    {
        $allowedLines = $this->getAllowedLines(
            $linesToBeCovered,
            $linesToBeUsed
        );
        $message = '';
        foreach ($data as $file => $_data) {
            foreach ($_data as $line => $flag) {
                if ($flag == 1 &&
                    (!isset($allowedLines[$file]) ||
                        !isset($allowedLines[$file][$line]))) {
                    $message .= sprintf(
                        '- %s:%d' . PHP_EOL,
                        $file,
                        $line
                    );
                }
            }
        }
        if (!empty($message)) {
            throw new PHP_CodeCoverage_Exception_UnintentionallyCoveredCode(
                $message
            );
        }
    }
    private function getAllowedLines(array $linesToBeCovered, array $linesToBeUsed)
    {
        $allowedLines = array();
        foreach (array_keys($linesToBeCovered) as $file) {
            if (!isset($allowedLines[$file])) {
                $allowedLines[$file] = array();
            }
            $allowedLines[$file] = array_merge(
                $allowedLines[$file],
                $linesToBeCovered[$file]
            );
        }
        foreach (array_keys($linesToBeUsed) as $file) {
            if (!isset($allowedLines[$file])) {
                $allowedLines[$file] = array();
            }
            $allowedLines[$file] = array_merge(
                $allowedLines[$file],
                $linesToBeUsed[$file]
            );
        }
        foreach (array_keys($allowedLines) as $file) {
            $allowedLines[$file] = array_flip(
                array_unique($allowedLines[$file])
            );
        }
        return $allowedLines;
    }
}
