<?php
abstract class PHP_CodeCoverage_Report_Node implements Countable
{
    protected $name;
    protected $path;
    protected $pathArray;
    protected $parent;
    protected $id;
    public function __construct($name, PHP_CodeCoverage_Report_Node $parent = null)
    {
        if (substr($name, -1) == '/') {
            $name = substr($name, 0, -1);
        }
        $this->name   = $name;
        $this->parent = $parent;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getId()
    {
        if ($this->id === null) {
            $parent = $this->getParent();
            if ($parent === null) {
                $this->id = 'index';
            } else {
                $parentId = $parent->getId();
                if ($parentId == 'index') {
                    $this->id = str_replace(':', '_', $this->name);
                } else {
                    $this->id = $parentId . '/' . $this->name;
                }
            }
        }
        return $this->id;
    }
    public function getPath()
    {
        if ($this->path === null) {
            if ($this->parent === null || $this->parent->getPath() === null) {
                $this->path = $this->name;
            } else {
                $this->path = $this->parent->getPath() . '/' . $this->name;
            }
        }
        return $this->path;
    }
    public function getPathAsArray()
    {
        if ($this->pathArray === null) {
            if ($this->parent === null) {
                $this->pathArray = array();
            } else {
                $this->pathArray = $this->parent->getPathAsArray();
            }
            $this->pathArray[] = $this;
        }
        return $this->pathArray;
    }
    public function getParent()
    {
        return $this->parent;
    }
    public function getTestedClassesPercent($asString = true)
    {
        return PHP_CodeCoverage_Util::percent(
            $this->getNumTestedClasses(),
            $this->getNumClasses(),
            $asString
        );
    }
    public function getTestedTraitsPercent($asString = true)
    {
        return PHP_CodeCoverage_Util::percent(
            $this->getNumTestedTraits(),
            $this->getNumTraits(),
            $asString
        );
    }
    public function getTestedClassesAndTraitsPercent($asString = true)
    {
        return PHP_CodeCoverage_Util::percent(
            $this->getNumTestedClassesAndTraits(),
            $this->getNumClassesAndTraits(),
            $asString
        );
    }
    public function getTestedMethodsPercent($asString = true)
    {
        return PHP_CodeCoverage_Util::percent(
            $this->getNumTestedMethods(),
            $this->getNumMethods(),
            $asString
        );
    }
    public function getLineExecutedPercent($asString = true)
    {
        return PHP_CodeCoverage_Util::percent(
            $this->getNumExecutedLines(),
            $this->getNumExecutableLines(),
            $asString
        );
    }
    public function getNumClassesAndTraits()
    {
        return $this->getNumClasses() + $this->getNumTraits();
    }
    public function getNumTestedClassesAndTraits()
    {
        return $this->getNumTestedClasses() + $this->getNumTestedTraits();
    }
    public function getClassesAndTraits()
    {
        return array_merge($this->getClasses(), $this->getTraits());
    }
    abstract public function getClasses();
    abstract public function getTraits();
    abstract public function getFunctions();
    abstract public function getLinesOfCode();
    abstract public function getNumExecutableLines();
    abstract public function getNumExecutedLines();
    abstract public function getNumClasses();
    abstract public function getNumTestedClasses();
    abstract public function getNumTraits();
    abstract public function getNumTestedTraits();
    abstract public function getNumMethods();
    abstract public function getNumTestedMethods();
    abstract public function getNumFunctions();
    abstract public function getNumTestedFunctions();
}
