<?php
class PHPUnit_Framework_MockObject_InvocationMocker implements PHPUnit_Framework_MockObject_Stub_MatcherCollection, PHPUnit_Framework_MockObject_Invokable, PHPUnit_Framework_MockObject_Builder_Namespace
{
    protected $matchers = array();
    protected $builderMap = array();
    public function addMatcher(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        $this->matchers[] = $matcher;
    }
    public function hasMatchers()
    {
        foreach ($this->matchers as $matcher) {
            if ($matcher->hasMatchers()) {
                return TRUE;
            }
        }
        return FALSE;
    }
    public function lookupId($id)
    {
        if (isset($this->builderMap[$id])) {
            return $this->builderMap[$id];
        }
        return NULL;
    }
    public function registerId($id, PHPUnit_Framework_MockObject_Builder_Match $builder)
    {
        if (isset($this->builderMap[$id])) {
            throw new PHPUnit_Framework_Exception(
              'Match builder with id <' . $id . '> is already registered.'
            );
        }
        $this->builderMap[$id] = $builder;
    }
    public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        return new PHPUnit_Framework_MockObject_Builder_InvocationMocker(
          $this, $matcher
        );
    }
    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $exception      = NULL;
        $hasReturnValue = FALSE;
        if (strtolower($invocation->methodName) == '__tostring') {
            $returnValue = '';
        } else {
            $returnValue = NULL;
        }
        foreach ($this->matchers as $match) {
            try {
                if ($match->matches($invocation)) {
                    $value = $match->invoked($invocation);
                    if (!$hasReturnValue) {
                        $returnValue    = $value;
                        $hasReturnValue = TRUE;
                    }
                }
            } catch (Exception $e) {
                $exception = $e;
            }
        }
        if ($exception !== NULL) {
            throw $exception;
        }
        return $returnValue;
    }
    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        foreach ($this->matchers as $matcher) {
            if (!$matcher->matches($invocation)) {
                return FALSE;
            }
        }
        return TRUE;
    }
    public function verify()
    {
        foreach ($this->matchers as $matcher) {
            $matcher->verify();
        }
    }
}
