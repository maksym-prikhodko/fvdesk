<?php
namespace Psy\Command\ListCommand;
use Psy\Formatter\SignatureFormatter;
use Psy\Presenter\PresenterManager;
use Psy\Util\Mirror;
use Symfony\Component\Console\Input\InputInterface;
abstract class Enumerator
{
    const IS_PUBLIC    = 'public';
    const IS_PROTECTED = 'protected';
    const IS_PRIVATE   = 'private';
    const IS_GLOBAL    = 'global';
    const IS_CONSTANT  = 'const';
    const IS_CLASS     = 'class';
    const IS_FUNCTION  = 'function';
    private $presenterManager;
    private $filter       = false;
    private $invertFilter = false;
    private $pattern;
    public function __construct(PresenterManager $presenterManager)
    {
        $this->presenterManager = $presenterManager;
    }
    public function enumerate(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        $this->setFilter($input);
        return $this->listItems($input, $reflector, $target);
    }
    abstract protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null);
    protected function presentRef($value)
    {
        return $this->presenterManager->presentRef($value);
    }
    protected function showItem($name)
    {
        return $this->filter === false || (preg_match($this->pattern, $name) xor $this->invertFilter);
    }
    private function setFilter(InputInterface $input)
    {
        if ($pattern = $input->getOption('grep')) {
            if (substr($pattern, 0, 1) !== '/' || substr($pattern, -1) !== '/' || strlen($pattern) < 3) {
                $pattern = '/' . preg_quote($pattern, '/') . '/';
            }
            if ($input->getOption('insensitive')) {
                $pattern .= 'i';
            }
            $this->validateRegex($pattern);
            $this->filter       = true;
            $this->pattern      = $pattern;
            $this->invertFilter = $input->getOption('invert');
        } else {
            $this->filter = false;
        }
    }
    private function validateRegex($pattern)
    {
        set_error_handler(array('Psy\Exception\ErrorException', 'throwException'));
        try {
            preg_match($pattern, '');
        } catch (ErrorException $e) {
            throw new RuntimeException(str_replace('preg_match(): ', 'Invalid regular expression: ', $e->getRawMessage()));
        }
        restore_error_handler();
    }
    protected function presentSignature($target)
    {
        if (!$target instanceof \Reflector) {
            $target = Mirror::get($target);
        }
        return SignatureFormatter::format($target);
    }
}
