<?php
class PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters
  extends PHPUnit_Framework_MockObject_Matcher_StatelessInvocation
{
  private $_parameterGroups = array();
  private $_invocations = array();
  public function __construct(array $parameterGroups)
  {
      foreach ($parameterGroups as $index => $parameters) {
          foreach ($parameters as $parameter) {
              if (!($parameter instanceof \PHPUnit_Framework_Constraint))
              {
                  $parameter = new \PHPUnit_Framework_Constraint_IsEqual($parameter);
              }
              $this->_parameterGroups[$index][] = $parameter;
          }
      }
  }
    public function toString()
    {
        $text = 'with consecutive parameters';
        return $text;
    }
  public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
  {
      $this->_invocations[] = $invocation;
      $callIndex = count($this->_invocations) - 1;
      $this->verifyInvocation($invocation, $callIndex);
      return FALSE;
  }
  public function verify()
  {
      foreach ($this->_invocations as $callIndex => $invocation) {
        $this->verifyInvocation($invocation, $callIndex);
      }
  }
  private function verifyInvocation(PHPUnit_Framework_MockObject_Invocation $invocation, $callIndex)
  {
      if (isset($this->_parameterGroups[$callIndex])) {
          $parameters = $this->_parameterGroups[$callIndex];
      } else {
        return;
      }
      if ($invocation === NULL) {
          throw new PHPUnit_Framework_ExpectationFailedException(
            'Mocked method does not exist.'
          );
      }
      if (count($invocation->parameters) < count($parameters)) {
          throw new PHPUnit_Framework_ExpectationFailedException(
              sprintf(
                'Parameter count for invocation %s is too low.',
                $invocation->toString()
              )
          );
      }
      foreach ($parameters as $i => $parameter) {
          $parameter->evaluate(
              $invocation->parameters[$i],
              sprintf(
                'Parameter %s for invocation #%d %s does not match expected ' .
                'value.',
                $i,
                $callIndex,
                $invocation->toString()
              )
          );
      }
  }
}
