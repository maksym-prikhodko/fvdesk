<?php
class PHP_CodeCoverage_Report_Node_Directory extends PHP_CodeCoverage_Report_Node implements IteratorAggregate
{
    protected $children = array();
    protected $directories = array();
    protected $files = array();
    protected $classes;
    protected $traits;
    protected $functions;
    protected $linesOfCode = null;
    protected $numFiles = -1;
    protected $numExecutableLines = -1;
    protected $numExecutedLines = -1;
    protected $numClasses = -1;
    protected $numTestedClasses = -1;
    protected $numTraits = -1;
    protected $numTestedTraits = -1;
    protected $numMethods = -1;
    protected $numTestedMethods = -1;
    protected $numFunctions = -1;
    protected $numTestedFunctions = -1;
    public function count()
    {
        if ($this->numFiles == -1) {
            $this->numFiles = 0;
            foreach ($this->children as $child) {
                $this->numFiles += count($child);
            }
        }
        return $this->numFiles;
    }
    public function getIterator()
    {
        return new RecursiveIteratorIterator(
            new PHP_CodeCoverage_Report_Node_Iterator($this),
            RecursiveIteratorIterator::SELF_FIRST
        );
    }
    public function addDirectory($name)
    {
        $directory = new PHP_CodeCoverage_Report_Node_Directory($name, $this);
        $this->children[]    = $directory;
        $this->directories[] = &$this->children[count($this->children) - 1];
        return $directory;
    }
    public function addFile($name, array $coverageData, array $testData, $cacheTokens)
    {
        $file = new PHP_CodeCoverage_Report_Node_File(
            $name,
            $this,
            $coverageData,
            $testData,
            $cacheTokens
        );
        $this->children[] = $file;
        $this->files[]    = &$this->children[count($this->children) - 1];
        $this->numExecutableLines = -1;
        $this->numExecutedLines   = -1;
        return $file;
    }
    public function getDirectories()
    {
        return $this->directories;
    }
    public function getFiles()
    {
        return $this->files;
    }
    public function getChildNodes()
    {
        return $this->children;
    }
    public function getClasses()
    {
        if ($this->classes === null) {
            $this->classes = array();
            foreach ($this->children as $child) {
                $this->classes = array_merge(
                    $this->classes,
                    $child->getClasses()
                );
            }
        }
        return $this->classes;
    }
    public function getTraits()
    {
        if ($this->traits === null) {
            $this->traits = array();
            foreach ($this->children as $child) {
                $this->traits = array_merge(
                    $this->traits,
                    $child->getTraits()
                );
            }
        }
        return $this->traits;
    }
    public function getFunctions()
    {
        if ($this->functions === null) {
            $this->functions = array();
            foreach ($this->children as $child) {
                $this->functions = array_merge(
                    $this->functions,
                    $child->getFunctions()
                );
            }
        }
        return $this->functions;
    }
    public function getLinesOfCode()
    {
        if ($this->linesOfCode === null) {
            $this->linesOfCode = array('loc' => 0, 'cloc' => 0, 'ncloc' => 0);
            foreach ($this->children as $child) {
                $linesOfCode = $child->getLinesOfCode();
                $this->linesOfCode['loc']   += $linesOfCode['loc'];
                $this->linesOfCode['cloc']  += $linesOfCode['cloc'];
                $this->linesOfCode['ncloc'] += $linesOfCode['ncloc'];
            }
        }
        return $this->linesOfCode;
    }
    public function getNumExecutableLines()
    {
        if ($this->numExecutableLines == -1) {
            $this->numExecutableLines = 0;
            foreach ($this->children as $child) {
                $this->numExecutableLines += $child->getNumExecutableLines();
            }
        }
        return $this->numExecutableLines;
    }
    public function getNumExecutedLines()
    {
        if ($this->numExecutedLines == -1) {
            $this->numExecutedLines = 0;
            foreach ($this->children as $child) {
                $this->numExecutedLines += $child->getNumExecutedLines();
            }
        }
        return $this->numExecutedLines;
    }
    public function getNumClasses()
    {
        if ($this->numClasses == -1) {
            $this->numClasses = 0;
            foreach ($this->children as $child) {
                $this->numClasses += $child->getNumClasses();
            }
        }
        return $this->numClasses;
    }
    public function getNumTestedClasses()
    {
        if ($this->numTestedClasses == -1) {
            $this->numTestedClasses = 0;
            foreach ($this->children as $child) {
                $this->numTestedClasses += $child->getNumTestedClasses();
            }
        }
        return $this->numTestedClasses;
    }
    public function getNumTraits()
    {
        if ($this->numTraits == -1) {
            $this->numTraits = 0;
            foreach ($this->children as $child) {
                $this->numTraits += $child->getNumTraits();
            }
        }
        return $this->numTraits;
    }
    public function getNumTestedTraits()
    {
        if ($this->numTestedTraits == -1) {
            $this->numTestedTraits = 0;
            foreach ($this->children as $child) {
                $this->numTestedTraits += $child->getNumTestedTraits();
            }
        }
        return $this->numTestedTraits;
    }
    public function getNumMethods()
    {
        if ($this->numMethods == -1) {
            $this->numMethods = 0;
            foreach ($this->children as $child) {
                $this->numMethods += $child->getNumMethods();
            }
        }
        return $this->numMethods;
    }
    public function getNumTestedMethods()
    {
        if ($this->numTestedMethods == -1) {
            $this->numTestedMethods = 0;
            foreach ($this->children as $child) {
                $this->numTestedMethods += $child->getNumTestedMethods();
            }
        }
        return $this->numTestedMethods;
    }
    public function getNumFunctions()
    {
        if ($this->numFunctions == -1) {
            $this->numFunctions = 0;
            foreach ($this->children as $child) {
                $this->numFunctions += $child->getNumFunctions();
            }
        }
        return $this->numFunctions;
    }
    public function getNumTestedFunctions()
    {
        if ($this->numTestedFunctions == -1) {
            $this->numTestedFunctions = 0;
            foreach ($this->children as $child) {
                $this->numTestedFunctions += $child->getNumTestedFunctions();
            }
        }
        return $this->numTestedFunctions;
    }
}
