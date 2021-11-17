<?php
class PHPUnit_Framework_MockObject_Matcher_MethodName extends PHPUnit_Framework_MockObject_Matcher_StatelessInvocation
{
    protected $constraint;
    public function __construct($constraint)
    {
        if (!$constraint instanceof PHPUnit_Framework_Constraint) {
            if (!is_string($constraint)) {
                throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
            }
            $constraint = new PHPUnit_Framework_Constraint_IsEqual(
              $constraint, 0, 10, FALSE, TRUE
            );
        }
        $this->constraint = $constraint;
    }
    public function toString()
    {
        return 'method name ' . $this->constraint->toString();
    }
    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        return $this->constraint->evaluate($invocation->methodName, '', TRUE);
    }
}
