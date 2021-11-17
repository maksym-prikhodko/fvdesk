<?php
namespace PhpSpec\Formatter\Presenter;
use Exception;
use PhpSpec\Exception\Exception as PhpSpecException;
use PhpSpec\Exception\Example\NotEqualException;
use PhpSpec\Exception\Example\ErrorException;
use PhpSpec\Exception\Example\PendingException;
use Prophecy\Argument\Token\ExactValueToken;
use Prophecy\Exception\Call\UnexpectedCallException;
use Prophecy\Exception\Exception as ProphecyException;
use Prophecy\Prophecy\MethodProphecy;
class StringPresenter implements PresenterInterface
{
    private $differ;
    private $phpspecPath;
    private $runnerPath;
    public function __construct(Differ\Differ $differ)
    {
        $this->differ = $differ;
        $this->phpspecPath = dirname(dirname(__DIR__));
        $this->runnerPath  = $this->phpspecPath.DIRECTORY_SEPARATOR.'Runner';
    }
    public function presentValue($value)
    {
        if (is_callable($value)) {
            return $this->presentString($this->presentCallable($value));
        }
        if ($value instanceof Exception) {
            return $this->presentString(sprintf(
                '[exc:%s("%s")]',
                get_class($value),
                $value->getMessage()
            ));
        }
        switch ($type = strtolower(gettype($value))) {
            case 'null':
                return $this->presentString('null');
            case 'boolean':
                return $this->presentString(sprintf('%s', true === $value ? 'true' : 'false'));
            case 'object':
                return $this->presentString(sprintf('[obj:%s]', get_class($value)));
            case 'array':
                return $this->presentString(sprintf('[array:%d]', count($value)));
            case 'string':
                if (25 > strlen($value) && false === strpos($value, "\n")) {
                    return $this->presentString(sprintf('"%s"', $value));
                }
                $lines = explode("\n", $value);
                return $this->presentString(sprintf('"%s..."', substr($lines[0], 0, 25)));
            default:
                return $this->presentString(sprintf('[%s:%s]', $type, $value));
        }
    }
    public function presentException(Exception $exception, $verbose = false)
    {
        if ($exception instanceof PhpSpecException) {
            $presentation = wordwrap($exception->getMessage(), 120);
        } elseif ($exception instanceof ProphecyException) {
            $presentation = $exception->getMessage();
        } else {
            $presentation = sprintf('Exception %s has been thrown.', $this->presentValue($exception));
        }
        if (!$verbose || $exception instanceof PendingException) {
            return $presentation;
        }
        if ($exception instanceof NotEqualException) {
            if ($diff = $this->presentExceptionDifference($exception)) {
                $presentation .= "\n".$diff;
            }
        }
        if ($exception instanceof PhpSpecException && !$exception instanceof ErrorException) {
            list($file, $line) = $this->getExceptionExamplePosition($exception);
            $presentation .= "\n".$this->presentFileCode($file, $line);
        }
        if ($exception instanceof UnexpectedCallException) {
            $presentation .= $this->presentCallArgumentsDifference($exception);
        }
        if (trim($trace = $this->presentExceptionStackTrace($exception))) {
            $presentation .= "\n".$trace;
        }
        return $presentation;
    }
    public function presentString($string)
    {
        return $string;
    }
    protected function presentFileCode($file, $lineno, $context = 6)
    {
        $lines  = explode("\n", file_get_contents($file));
        $offset = max(0, $lineno - ceil($context / 2));
        $lines  = array_slice($lines, $offset, $context);
        $text = "\n";
        foreach ($lines as $line) {
            $offset++;
            if ($offset == $lineno) {
                $text .= $this->presentHighlight(sprintf('%4d', $offset).' '.$line);
            } else {
                $text .= $this->presentCodeLine(sprintf('%4d', $offset), $line);
            }
            $text .= "\n";
        }
        return $text;
    }
    protected function presentCodeLine($number, $line)
    {
        return $number.' '.$line;
    }
    protected function presentHighlight($line)
    {
        return $line;
    }
    protected function presentExceptionDifference(NotEqualException $exception)
    {
        return $this->differ->compare($exception->getExpected(), $exception->getActual());
    }
    protected function presentExceptionStackTrace(Exception $exception)
    {
        $offset = 0;
        $text   = "\n";
        $text .= $this->presentExceptionTraceLocation($offset++, $exception->getFile(), $exception->getLine());
        $text .= $this->presentExceptionTraceFunction(
            'throw new '.get_class($exception),
            array($exception->getMessage())
        );
        foreach ($exception->getTrace() as $call) {
            if ($this->shouldStopTracePresentation($call)) {
                break;
            }
            if ($this->shouldSkipTracePresentation($call)) {
                continue;
            }
            if (isset($call['file'])) {
                $text .= $this->presentExceptionTraceLocation($offset++, $call['file'], $call['line']);
            } else {
                $text .= $this->presentExceptionTraceHeader(sprintf("%2d [internal]", $offset++));
            }
            if (isset($call['class'])) {
                $text .= $this->presentExceptionTraceMethod(
                    $call['class'],
                    $call['type'],
                    $call['function'],
                    isset($call['args']) ? $call['args'] : array()
                );
            } elseif (isset($call['function'])) {
                $text .= $this->presentExceptionTraceFunction(
                    $call['function'],
                    isset($call['args']) ? $call['args'] : array()
                );
            }
        }
        return $text;
    }
    protected function presentExceptionTraceHeader($header)
    {
        return $header."\n";
    }
    protected function presentExceptionTraceMethod($class, $type, $method, array $args)
    {
        $args = array_map(array($this, 'presentValue'), $args);
        return sprintf("   %s%s%s(%s)\n", $class, $type, $method, implode(', ', $args));
    }
    protected function presentExceptionTraceFunction($function, array $args)
    {
        $args = array_map(array($this, 'presentValue'), $args);
        return sprintf("   %s(%s)\n", $function, implode(', ', $args));
    }
    protected function getExceptionExamplePosition(PhpSpecException $exception)
    {
        $refl = $exception->getCause();
        foreach ($exception->getTrace() as $call) {
            if (!isset($call['file'])) {
                continue;
            }
            if (!empty($refl) && $refl->getFilename() === $call['file']) {
                return array($call['file'], $call['line']);
            }
        }
        return array($exception->getFile(), $exception->getLine());
    }
    private function presentExceptionTraceLocation($offset, $file, $line)
    {
        return $this->presentExceptionTraceHeader(sprintf(
            "%2d %s:%d",
            $offset,
            str_replace(getcwd().DIRECTORY_SEPARATOR, '', $file),
            $line
        ));
    }
    private function shouldStopTracePresentation(array $call)
    {
        return isset($call['file']) && false !== strpos($call['file'], $this->runnerPath);
    }
    private function shouldSkipTracePresentation(array $call)
    {
        if (isset($call['file']) && 0 === strpos($call['file'], $this->phpspecPath)) {
            return true;
        }
        return isset($call['class']) && 0 === strpos($call['class'], "PhpSpec\\");
    }
    private function presentCallable($value)
    {
        if (is_array($value)) {
            $type = is_object($value[0]) ? $this->presentValue($value[0]) : $value[0];
            return sprintf('%s::%s()', $type, $value[1]);
        }
        if ($value instanceof \Closure) {
            return '[closure]';
        }
        if (is_object($value)) {
            return sprintf('[obj:%s]', get_class($value));
        }
        return sprintf('[%s()]', $value);
    }
    private function presentCallArgumentsDifference(UnexpectedCallException $exception)
    {
        $actualArguments = $exception->getArguments();
        $methodProphecies = $exception->getObjectProphecy()->getMethodProphecies($exception->getMethodName());
        if ($this->noMethodPropheciesForUnexpectedCall($methodProphecies)) {
            return '';
        }
        $presentedMethodProphecy = $this->findMethodProphecyOfFirstNotExpectedArgumentsCall($methodProphecies, $exception);
        $expectedTokens = $presentedMethodProphecy->getArgumentsWildcard()->getTokens();
        if ($this->parametersCountMismatch($expectedTokens, $actualArguments)) {
            return '';
        }
        $expectedArguments = $this->convertArgumentTokensToDiffableValues($expectedTokens);
        $text = $this->generateArgumentsDifferenceText($actualArguments, $expectedArguments);
        return $text;
    }
    private function noMethodPropheciesForUnexpectedCall(array $methodProphecies)
    {
        return count($methodProphecies) === 0;
    }
    private function findMethodProphecyOfFirstNotExpectedArgumentsCall(array $methodProphecies, UnexpectedCallException $exception)
    {
        $objectProphecy = $exception->getObjectProphecy();
        foreach ($methodProphecies as $methodProphecy) {
            $calls = $objectProphecy->findProphecyMethodCalls(
                $exception->getMethodName(),
                $methodProphecy->getArgumentsWildcard()
            );
            if (count($calls)) {
                continue;
            }
            return $methodProphecy;
        }
    }
    private function parametersCountMismatch(array $expectedTokens, array $actualArguments)
    {
        return count($expectedTokens) !== count($actualArguments);
    }
    private function convertArgumentTokensToDiffableValues(array $tokens)
    {
        $values = array();
        foreach ($tokens as $token) {
            if ($token instanceof ExactValueToken) {
                $values[] = $token->getValue();
            } else {
                $values[] = (string)$token;
            }
        }
        return $values;
    }
    private function generateArgumentsDifferenceText(array $actualArguments, array $expectedArguments)
    {
        $text = '';
        foreach($actualArguments as $i => $actualArgument) {
            $expectedArgument = $expectedArguments[$i];
            $actualArgument = is_null($actualArgument) ? 'null' : $actualArgument;
            $expectedArgument = is_null($expectedArgument) ? 'null' : $expectedArgument;
            $text .= $this->differ->compare($expectedArgument, $actualArgument);
        }
        return $text;
    }
}
