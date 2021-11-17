<?php
class PHPUnit_Framework_MockObject_Matcher implements PHPUnit_Framework_MockObject_Matcher_Invocation
{
    public $invocationMatcher;
    public $afterMatchBuilderId = NULL;
    public $afterMatchBuilderIsInvoked = FALSE;
    public $methodNameMatcher = NULL;
    public $parametersMatcher = NULL;
    public $stub = NULL;
    public function __construct(PHPUnit_Framework_MockObject_Matcher_Invocation $invocationMatcher)
    {
        $this->invocationMatcher = $invocationMatcher;
    }
    public function toString()
    {
        $list = array();
        if ($this->invocationMatcher !== NULL) {
            $list[] = $this->invocationMatcher->toString();
        }
        if ($this->methodNameMatcher !== NULL) {
            $list[] = 'where ' . $this->methodNameMatcher->toString();
        }
        if ($this->parametersMatcher !== NULL) {
            $list[] = 'and ' . $this->parametersMatcher->toString();
        }
        if ($this->afterMatchBuilderId !== NULL) {
            $list[] = 'after ' . $this->afterMatchBuilderId;
        }
        if ($this->stub !== NULL) {
            $list[] = 'will ' . $this->stub->toString();
        }
        return join(' ', $list);
    }
    public function invoked(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        if ($this->invocationMatcher === NULL) {
            throw new PHPUnit_Framework_Exception(
              'No invocation matcher is set'
            );
        }
        if ($this->methodNameMatcher === NULL) {
            throw new PHPUnit_Framework_Exception('No method matcher is set');
        }
        if ($this->afterMatchBuilderId !== NULL) {
            $builder = $invocation->object
                                  ->__phpunit_getInvocationMocker()
                                  ->lookupId($this->afterMatchBuilderId);
            if (!$builder) {
                throw new PHPUnit_Framework_Exception(
                  sprintf(
                    'No builder found for match builder identification <%s>',
                    $this->afterMatchBuilderId
                  )
                );
            }
            $matcher = $builder->getMatcher();
            if ($matcher && $matcher->invocationMatcher->hasBeenInvoked()) {
                $this->afterMatchBuilderIsInvoked = TRUE;
            }
        }
        $this->invocationMatcher->invoked($invocation);
        try {
            if ( $this->parametersMatcher !== NULL &&
                !$this->parametersMatcher->matches($invocation)) {
                $this->parametersMatcher->verify();
            }
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            throw new PHPUnit_Framework_ExpectationFailedException(
              sprintf(
                "Expectation failed for %s when %s\n%s",
                $this->methodNameMatcher->toString(),
                $this->invocationMatcher->toString(),
                $e->getMessage()
              ),
              $e->getComparisonFailure()
            );
        }
        if ($this->stub) {
            return $this->stub->invoke($invocation);
        }
        return NULL;
    }
    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        if ($this->afterMatchBuilderId !== NULL) {
            $builder = $invocation->object
                                  ->__phpunit_getInvocationMocker()
                                  ->lookupId($this->afterMatchBuilderId);
            if (!$builder) {
                throw new PHPUnit_Framework_Exception(
                  sprintf(
                    'No builder found for match builder identification <%s>',
                    $this->afterMatchBuilderId
                  )
                );
            }
            $matcher = $builder->getMatcher();
            if (!$matcher) {
                return FALSE;
            }
            if (!$matcher->invocationMatcher->hasBeenInvoked()) {
                return FALSE;
            }
        }
        if ($this->invocationMatcher === NULL) {
            throw new PHPUnit_Framework_Exception(
              'No invocation matcher is set'
            );
        }
        if ($this->methodNameMatcher === NULL) {
            throw new PHPUnit_Framework_Exception('No method matcher is set');
        }
        if (!$this->invocationMatcher->matches($invocation)) {
            return FALSE;
        }
        try {
            if (!$this->methodNameMatcher->matches($invocation)) {
                return FALSE;
            }
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            throw new PHPUnit_Framework_ExpectationFailedException(
              sprintf(
                "Expectation failed for %s when %s\n%s",
                $this->methodNameMatcher->toString(),
                $this->invocationMatcher->toString(),
                $e->getMessage()
              ),
              $e->getComparisonFailure()
            );
        }
        return TRUE;
    }
    public function verify()
    {
        if ($this->invocationMatcher === NULL) {
            throw new PHPUnit_Framework_Exception(
              'No invocation matcher is set'
            );
        }
        if ($this->methodNameMatcher === NULL) {
            throw new PHPUnit_Framework_Exception('No method matcher is set');
        }
        try {
            $this->invocationMatcher->verify();
            if ($this->parametersMatcher === NULL) {
                $this->parametersMatcher = new PHPUnit_Framework_MockObject_Matcher_AnyParameters;
            }
            $invocationIsAny = get_class($this->invocationMatcher) === 'PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount';
            $invocationIsNever = get_class($this->invocationMatcher) === 'PHPUnit_Framework_MockObject_Matcher_InvokedCount' && $this->invocationMatcher->isNever();
            if (!$invocationIsAny && !$invocationIsNever) {
                $this->parametersMatcher->verify();
            }
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            throw new PHPUnit_Framework_ExpectationFailedException(
              sprintf(
                "Expectation failed for %s when %s.\n%s",
                $this->methodNameMatcher->toString(),
                $this->invocationMatcher->toString(),
                PHPUnit_Framework_TestFailure::exceptionToString($e)
              )
            );
        }
    }
    public function hasMatchers()
    {
        if ($this->invocationMatcher !== NULL &&
            !$this->invocationMatcher instanceof PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount) {
            return TRUE;
        }
        return FALSE;
    }
}
