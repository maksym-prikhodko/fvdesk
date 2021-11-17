<?php
namespace PhpSpec\Exception\Fracture;
use Exception;
use ReflectionParameter;
class CollaboratorNotFoundException extends FractureException
{
    const CLASSNAME_REGEX = '/\\[.* (?P<classname>[_a-z0-9\\\\]+) .*\\]/i';
    private $collaboratorName;
    public function __construct($message, $code = 0, Exception $previous = null, ReflectionParameter $reflectionParameter = null)
    {
        if ($reflectionParameter) {
            $this->collaboratorName = $this->extractCollaboratorName($reflectionParameter);
        }
        parent::__construct($message . ': ' . $this->collaboratorName, $code, $previous);
    }
    public function getCollaboratorName()
    {
        return $this->collaboratorName;
    }
    private function extractCollaboratorName(ReflectionParameter $parameter)
    {
        if (preg_match(self::CLASSNAME_REGEX, (string)$parameter, $matches)) {
            return $matches['classname'];
        }
        return 'Unknown class';
    }
}
