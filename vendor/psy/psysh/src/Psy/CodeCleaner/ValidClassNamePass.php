<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_ as NewExpr;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_ as ClassStmt;
use PhpParser\Node\Stmt\Interface_ as InterfaceStmt;
use PhpParser\Node\Stmt\Trait_ as TraitStmt;
use Psy\Exception\FatalErrorException;
class ValidClassNamePass extends NamespaceAwarePass
{
    const CLASS_TYPE     = 'class';
    const INTERFACE_TYPE = 'interface';
    const TRAIT_TYPE     = 'trait';
    protected $checkTraits;
    public function __construct()
    {
        $this->checkTraits = function_exists('trait_exists');
    }
    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassStmt) {
            $this->validateClassStatement($node);
        } elseif ($node instanceof InterfaceStmt) {
            $this->validateInterfaceStatement($node);
        } elseif ($node instanceof TraitStmt) {
            $this->validateTraitStatement($node);
        } elseif ($node instanceof NewExpr) {
            $this->validateNewExpression($node);
        } elseif ($node instanceof ClassConstFetch) {
            $this->validateClassConstFetchExpression($node);
        } elseif ($node instanceof StaticCall) {
            $this->validateStaticCallExpression($node);
        }
    }
    protected function validateClassStatement(ClassStmt $stmt)
    {
        $this->ensureCanDefine($stmt);
        if (isset($stmt->extends)) {
            $this->ensureClassExists($this->getFullyQualifiedName($stmt->extends), $stmt);
        }
        $this->ensureInterfacesExist($stmt->implements, $stmt);
    }
    protected function validateInterfaceStatement(InterfaceStmt $stmt)
    {
        $this->ensureCanDefine($stmt);
        $this->ensureInterfacesExist($stmt->extends, $stmt);
    }
    protected function validateTraitStatement(TraitStmt $stmt)
    {
        $this->ensureCanDefine($stmt);
    }
    protected function validateNewExpression(NewExpr $stmt)
    {
        if (!$stmt->class instanceof Expr) {
            $this->ensureClassExists($this->getFullyQualifiedName($stmt->class), $stmt);
        }
    }
    protected function validateClassConstFetchExpression(ClassConstFetch $stmt)
    {
        if (!$stmt->class instanceof Expr) {
            $this->ensureClassExists($this->getFullyQualifiedName($stmt->class), $stmt);
        }
    }
    protected function validateStaticCallExpression(StaticCall $stmt)
    {
        if (!$stmt->class instanceof Expr) {
            $this->ensureMethodExists($this->getFullyQualifiedName($stmt->class), $stmt->name, $stmt);
        }
    }
    protected function ensureCanDefine(Stmt $stmt)
    {
        $name = $this->getFullyQualifiedName($stmt->name);
        $errorType = null;
        if ($this->classExists($name)) {
            $errorType = self::CLASS_TYPE;
        } elseif ($this->interfaceExists($name)) {
            $errorType = self::INTERFACE_TYPE;
        } elseif ($this->traitExists($name)) {
            $errorType = self::TRAIT_TYPE;
        }
        if ($errorType !== null) {
            throw $this->createError(sprintf('%s named %s already exists', ucfirst($errorType), $name), $stmt);
        }
        $this->currentScope[strtolower($name)] = $this->getScopeType($stmt);
    }
    protected function ensureClassExists($name, $stmt)
    {
        if (!$this->classExists($name)) {
            throw $this->createError(sprintf('Class \'%s\' not found', $name), $stmt);
        }
    }
    protected function ensureMethodExists($class, $name, $stmt)
    {
        $this->ensureClassExists($class, $stmt);
        if ($name instanceof Expr) {
            return;
        }
        if (!method_exists($class, $name) && !method_exists($class, '__callStatic')) {
            throw $this->createError(sprintf('Call to undefined method %s::%s()', $class, $name), $stmt);
        }
    }
    protected function ensureInterfacesExist($interfaces, $stmt)
    {
        foreach ($interfaces as $interface) {
            $name = $this->getFullyQualifiedName($interface);
            if (!$this->interfaceExists($name)) {
                throw $this->createError(sprintf('Interface \'%s\' not found', $name), $stmt);
            }
        }
    }
    protected function getScopeType(Stmt $stmt)
    {
        if ($stmt instanceof ClassStmt) {
            return self::CLASS_TYPE;
        } elseif ($stmt instanceof InterfaceStmt) {
            return self::INTERFACE_TYPE;
        } elseif ($stmt instanceof TraitStmt) {
            return self::TRAIT_TYPE;
        }
    }
    protected function classExists($name)
    {
        return class_exists($name) || $this->findInScope($name) === self::CLASS_TYPE;
    }
    protected function interfaceExists($name)
    {
        return interface_exists($name) || $this->findInScope($name) === self::INTERFACE_TYPE;
    }
    protected function traitExists($name)
    {
        return $this->checkTraits && (trait_exists($name) || $this->findInScope($name) === self::TRAIT_TYPE);
    }
    protected function findInScope($name)
    {
        $name = strtolower($name);
        if (isset($this->currentScope[$name])) {
            return $this->currentScope[$name];
        }
    }
    protected function createError($msg, $stmt)
    {
        return new FatalErrorException($msg, 0, 1, null, $stmt->getLine());
    }
}
