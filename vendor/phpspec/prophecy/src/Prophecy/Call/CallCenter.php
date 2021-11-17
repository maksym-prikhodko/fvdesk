<?php
namespace Prophecy\Call;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Argument\ArgumentsWildcard;
use Prophecy\Util\StringUtil;
use Prophecy\Exception\Call\UnexpectedCallException;
class CallCenter
{
    private $util;
    private $recordedCalls = array();
    public function __construct(StringUtil $util = null)
    {
        $this->util = $util ?: new StringUtil;
    }
    public function makeCall(ObjectProphecy $prophecy, $methodName, array $arguments)
    {
        $backtrace = debug_backtrace();
        $file = $line = null;
        if (isset($backtrace[2]) && isset($backtrace[2]['file'])) {
            $file = $backtrace[2]['file'];
            $line = $backtrace[2]['line'];
        }
        if ('__destruct' === $methodName || 0 == count($prophecy->getMethodProphecies())) {
            $this->recordedCalls[] = new Call($methodName, $arguments, null, null, $file, $line);
            return null;
        }
        $matches = array();
        foreach ($prophecy->getMethodProphecies($methodName) as $methodProphecy) {
            if (0 < $score = $methodProphecy->getArgumentsWildcard()->scoreArguments($arguments)) {
                $matches[] = array($score, $methodProphecy);
            }
        }
        if (!count($matches)) {
            throw $this->createUnexpectedCallException($prophecy, $methodName, $arguments);
        }
        @usort($matches, function ($match1, $match2) { return $match2[0] - $match1[0]; });
        $returnValue = null;
        $exception   = null;
        if ($promise = $matches[0][1]->getPromise()) {
            try {
                $returnValue = $promise->execute($arguments, $prophecy, $matches[0][1]);
            } catch (\Exception $e) {
                $exception = $e;
            }
        }
        $this->recordedCalls[] = new Call(
            $methodName, $arguments, $returnValue, $exception, $file, $line
        );
        if (null !== $exception) {
            throw $exception;
        }
        return $returnValue;
    }
    public function findCalls($methodName, ArgumentsWildcard $wildcard)
    {
        return array_values(
            array_filter($this->recordedCalls, function (Call $call) use ($methodName, $wildcard) {
                return $methodName === $call->getMethodName()
                    && 0 < $wildcard->scoreArguments($call->getArguments())
                ;
            })
        );
    }
    private function createUnexpectedCallException(ObjectProphecy $prophecy, $methodName,
                                                   array $arguments)
    {
        $classname = get_class($prophecy->reveal());
        $argstring = implode(', ', array_map(array($this->util, 'stringify'), $arguments));
        $expected  = implode("\n", array_map(function (MethodProphecy $methodProphecy) {
            return sprintf('  - %s(%s)',
                $methodProphecy->getMethodName(),
                $methodProphecy->getArgumentsWildcard()
            );
        }, call_user_func_array('array_merge', $prophecy->getMethodProphecies())));
        return new UnexpectedCallException(
            sprintf(
                "Method call:\n".
                "  - %s(%s)\n".
                "on %s was not expected, expected calls were:\n%s",
                $methodName, $argstring, $classname, $expected
            ),
            $prophecy, $methodName, $arguments
        );
    }
}
