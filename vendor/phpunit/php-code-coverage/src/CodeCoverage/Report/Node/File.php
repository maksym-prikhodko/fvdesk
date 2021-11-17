<?php
class PHP_CodeCoverage_Report_Node_File extends PHP_CodeCoverage_Report_Node
{
    protected $coverageData;
    protected $testData;
    protected $numExecutableLines = 0;
    protected $numExecutedLines = 0;
    protected $classes = array();
    protected $traits = array();
    protected $functions = array();
    protected $linesOfCode = array();
    protected $numTestedTraits = 0;
    protected $numTestedClasses = 0;
    protected $numMethods = null;
    protected $numTestedMethods = null;
    protected $numTestedFunctions = null;
    protected $startLines = array();
    protected $endLines = array();
    protected $cacheTokens;
    public function __construct($name, PHP_CodeCoverage_Report_Node $parent, array $coverageData, array $testData, $cacheTokens)
    {
        if (!is_bool($cacheTokens)) {
            throw PHP_CodeCoverage_Util_InvalidArgumentHelper::factory(
                1,
                'boolean'
            );
        }
        parent::__construct($name, $parent);
        $this->coverageData = $coverageData;
        $this->testData     = $testData;
        $this->cacheTokens  = $cacheTokens;
        $this->calculateStatistics();
    }
    public function count()
    {
        return 1;
    }
    public function getCoverageData()
    {
        return $this->coverageData;
    }
    public function getTestData()
    {
        return $this->testData;
    }
    public function getClasses()
    {
        return $this->classes;
    }
    public function getTraits()
    {
        return $this->traits;
    }
    public function getFunctions()
    {
        return $this->functions;
    }
    public function getLinesOfCode()
    {
        return $this->linesOfCode;
    }
    public function getNumExecutableLines()
    {
        return $this->numExecutableLines;
    }
    public function getNumExecutedLines()
    {
        return $this->numExecutedLines;
    }
    public function getNumClasses()
    {
        return count($this->classes);
    }
    public function getNumTestedClasses()
    {
        return $this->numTestedClasses;
    }
    public function getNumTraits()
    {
        return count($this->traits);
    }
    public function getNumTestedTraits()
    {
        return $this->numTestedTraits;
    }
    public function getNumMethods()
    {
        if ($this->numMethods === null) {
            $this->numMethods = 0;
            foreach ($this->classes as $class) {
                foreach ($class['methods'] as $method) {
                    if ($method['executableLines'] > 0) {
                        $this->numMethods++;
                    }
                }
            }
            foreach ($this->traits as $trait) {
                foreach ($trait['methods'] as $method) {
                    if ($method['executableLines'] > 0) {
                        $this->numMethods++;
                    }
                }
            }
        }
        return $this->numMethods;
    }
    public function getNumTestedMethods()
    {
        if ($this->numTestedMethods === null) {
            $this->numTestedMethods = 0;
            foreach ($this->classes as $class) {
                foreach ($class['methods'] as $method) {
                    if ($method['executableLines'] > 0 &&
                        $method['coverage'] == 100) {
                        $this->numTestedMethods++;
                    }
                }
            }
            foreach ($this->traits as $trait) {
                foreach ($trait['methods'] as $method) {
                    if ($method['executableLines'] > 0 &&
                        $method['coverage'] == 100) {
                        $this->numTestedMethods++;
                    }
                }
            }
        }
        return $this->numTestedMethods;
    }
    public function getNumFunctions()
    {
        return count($this->functions);
    }
    public function getNumTestedFunctions()
    {
        if ($this->numTestedFunctions === null) {
            $this->numTestedFunctions = 0;
            foreach ($this->functions as $function) {
                if ($function['executableLines'] > 0 &&
                    $function['coverage'] == 100) {
                    $this->numTestedFunctions++;
                }
            }
        }
        return $this->numTestedFunctions;
    }
    protected function calculateStatistics()
    {
        if ($this->cacheTokens) {
            $tokens = PHP_Token_Stream_CachingFactory::get($this->getPath());
        } else {
            $tokens = new PHP_Token_Stream($this->getPath());
        }
        $this->processClasses($tokens);
        $this->processTraits($tokens);
        $this->processFunctions($tokens);
        $this->linesOfCode = $tokens->getLinesOfCode();
        unset($tokens);
        for ($lineNumber = 1; $lineNumber <= $this->linesOfCode['loc']; $lineNumber++) {
            if (isset($this->startLines[$lineNumber])) {
                if (isset($this->startLines[$lineNumber]['className'])) {
                    $currentClass = &$this->startLines[$lineNumber];
                } 
                elseif (isset($this->startLines[$lineNumber]['traitName'])) {
                    $currentTrait = &$this->startLines[$lineNumber];
                } 
                elseif (isset($this->startLines[$lineNumber]['methodName'])) {
                    $currentMethod = &$this->startLines[$lineNumber];
                } 
                elseif (isset($this->startLines[$lineNumber]['functionName'])) {
                    $currentFunction = &$this->startLines[$lineNumber];
                }
            }
            if (isset($this->coverageData[$lineNumber]) &&
                $this->coverageData[$lineNumber] !== null) {
                if (isset($currentClass)) {
                    $currentClass['executableLines']++;
                }
                if (isset($currentTrait)) {
                    $currentTrait['executableLines']++;
                }
                if (isset($currentMethod)) {
                    $currentMethod['executableLines']++;
                }
                if (isset($currentFunction)) {
                    $currentFunction['executableLines']++;
                }
                $this->numExecutableLines++;
                if (count($this->coverageData[$lineNumber]) > 0) {
                    if (isset($currentClass)) {
                        $currentClass['executedLines']++;
                    }
                    if (isset($currentTrait)) {
                        $currentTrait['executedLines']++;
                    }
                    if (isset($currentMethod)) {
                        $currentMethod['executedLines']++;
                    }
                    if (isset($currentFunction)) {
                        $currentFunction['executedLines']++;
                    }
                    $this->numExecutedLines++;
                }
            }
            if (isset($this->endLines[$lineNumber])) {
                if (isset($this->endLines[$lineNumber]['className'])) {
                    unset($currentClass);
                } 
                elseif (isset($this->endLines[$lineNumber]['traitName'])) {
                    unset($currentTrait);
                } 
                elseif (isset($this->endLines[$lineNumber]['methodName'])) {
                    unset($currentMethod);
                } 
                elseif (isset($this->endLines[$lineNumber]['functionName'])) {
                    unset($currentFunction);
                }
            }
        }
        foreach ($this->traits as &$trait) {
            foreach ($trait['methods'] as &$method) {
                if ($method['executableLines'] > 0) {
                    $method['coverage'] = ($method['executedLines'] /
                            $method['executableLines']) * 100;
                } else {
                    $method['coverage'] = 100;
                }
                $method['crap'] = $this->crap(
                    $method['ccn'],
                    $method['coverage']
                );
                $trait['ccn'] += $method['ccn'];
            }
            if ($trait['executableLines'] > 0) {
                $trait['coverage'] = ($trait['executedLines'] /
                        $trait['executableLines']) * 100;
            } else {
                $trait['coverage'] = 100;
            }
            if ($trait['coverage'] == 100) {
                $this->numTestedClasses++;
            }
            $trait['crap'] = $this->crap(
                $trait['ccn'],
                $trait['coverage']
            );
        }
        foreach ($this->classes as &$class) {
            foreach ($class['methods'] as &$method) {
                if ($method['executableLines'] > 0) {
                    $method['coverage'] = ($method['executedLines'] /
                            $method['executableLines']) * 100;
                } else {
                    $method['coverage'] = 100;
                }
                $method['crap'] = $this->crap(
                    $method['ccn'],
                    $method['coverage']
                );
                $class['ccn'] += $method['ccn'];
            }
            if ($class['executableLines'] > 0) {
                $class['coverage'] = ($class['executedLines'] /
                        $class['executableLines']) * 100;
            } else {
                $class['coverage'] = 100;
            }
            if ($class['coverage'] == 100) {
                $this->numTestedClasses++;
            }
            $class['crap'] = $this->crap(
                $class['ccn'],
                $class['coverage']
            );
        }
    }
    protected function processClasses(PHP_Token_Stream $tokens)
    {
        $classes = $tokens->getClasses();
        unset($tokens);
        $link = $this->getId() . '.html#';
        foreach ($classes as $className => $class) {
            $this->classes[$className] = array(
                'className'       => $className,
                'methods'         => array(),
                'startLine'       => $class['startLine'],
                'executableLines' => 0,
                'executedLines'   => 0,
                'ccn'             => 0,
                'coverage'        => 0,
                'crap'            => 0,
                'package'         => $class['package'],
                'link'            => $link . $class['startLine']
            );
            $this->startLines[$class['startLine']] = &$this->classes[$className];
            $this->endLines[$class['endLine']]     = &$this->classes[$className];
            foreach ($class['methods'] as $methodName => $method) {
                $this->classes[$className]['methods'][$methodName] = array(
                    'methodName'      => $methodName,
                    'signature'       => $method['signature'],
                    'startLine'       => $method['startLine'],
                    'endLine'         => $method['endLine'],
                    'executableLines' => 0,
                    'executedLines'   => 0,
                    'ccn'             => $method['ccn'],
                    'coverage'        => 0,
                    'crap'            => 0,
                    'link'            => $link . $method['startLine']
                );
                $this->startLines[$method['startLine']] = &$this->classes[$className]['methods'][$methodName];
                $this->endLines[$method['endLine']]     = &$this->classes[$className]['methods'][$methodName];
            }
        }
    }
    protected function processTraits(PHP_Token_Stream $tokens)
    {
        $traits = $tokens->getTraits();
        unset($tokens);
        $link = $this->getId() . '.html#';
        foreach ($traits as $traitName => $trait) {
            $this->traits[$traitName] = array(
                'traitName'       => $traitName,
                'methods'         => array(),
                'startLine'       => $trait['startLine'],
                'executableLines' => 0,
                'executedLines'   => 0,
                'ccn'             => 0,
                'coverage'        => 0,
                'crap'            => 0,
                'package'         => $trait['package'],
                'link'            => $link . $trait['startLine']
            );
            $this->startLines[$trait['startLine']] = &$this->traits[$traitName];
            $this->endLines[$trait['endLine']]     = &$this->traits[$traitName];
            foreach ($trait['methods'] as $methodName => $method) {
                $this->traits[$traitName]['methods'][$methodName] = array(
                    'methodName'      => $methodName,
                    'signature'       => $method['signature'],
                    'startLine'       => $method['startLine'],
                    'endLine'         => $method['endLine'],
                    'executableLines' => 0,
                    'executedLines'   => 0,
                    'ccn'             => $method['ccn'],
                    'coverage'        => 0,
                    'crap'            => 0,
                    'link'            => $link . $method['startLine']
                );
                $this->startLines[$method['startLine']] = &$this->traits[$traitName]['methods'][$methodName];
                $this->endLines[$method['endLine']]     = &$this->traits[$traitName]['methods'][$methodName];
            }
        }
    }
    protected function processFunctions(PHP_Token_Stream $tokens)
    {
        $functions = $tokens->getFunctions();
        unset($tokens);
        $link = $this->getId() . '.html#';
        foreach ($functions as $functionName => $function) {
            $this->functions[$functionName] = array(
                'functionName'    => $functionName,
                'signature'       => $function['signature'],
                'startLine'       => $function['startLine'],
                'executableLines' => 0,
                'executedLines'   => 0,
                'ccn'             => $function['ccn'],
                'coverage'        => 0,
                'crap'            => 0,
                'link'            => $link . $function['startLine']
            );
            $this->startLines[$function['startLine']] = &$this->functions[$functionName];
            $this->endLines[$function['endLine']]     = &$this->functions[$functionName];
        }
    }
    protected function crap($ccn, $coverage)
    {
        if ($coverage == 0) {
            return (string) pow($ccn, 2) + $ccn;
        }
        if ($coverage >= 95) {
            return (string) $ccn;
        }
        return sprintf(
            '%01.2F',
            pow($ccn, 2) * pow(1 - $coverage/100, 3) + $ccn
        );
    }
}
