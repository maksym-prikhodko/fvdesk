<?php
namespace Psy\TabCompletion;
use Psy\TabCompletion\Matcher\AbstractMatcher;
class AutoCompleter
{
    protected $matchers;
    public function addMatcher(AbstractMatcher $matcher)
    {
        $this->matchers[] = $matcher;
    }
    public function activate()
    {
        readline_completion_function(array(&$this, 'callback'));
    }
    public function processCallback($input, $index, $info = array())
    {
        $line = substr($info['line_buffer'], 0, $info['end']);
        $tokens = token_get_all('<?php ' . $line);
        $tokens = array_filter($tokens, function ($token) {
            return !AbstractMatcher::tokenIs($token, AbstractMatcher::T_WHITESPACE);
        });
        $matches = array();
        foreach ($this->matchers as $matcher) {
            if ($matcher->hasMatched($tokens)) {
                $matches = array_merge($matcher->getMatches($tokens), $matches);
            }
        }
        $matches = array_unique($matches);
        return !empty($matches) ? $matches : array('');
    }
    public function callback($input, $index)
    {
        return $this->processCallback($input, $index, readline_info());
    }
    public function __destruct()
    {
        if (function_exists('readline_callback_handler_remove')) {
            readline_callback_handler_remove();
        }
    }
}