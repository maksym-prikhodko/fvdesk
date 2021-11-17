<?php
namespace Psy\Test\TabCompletion;
use Psy\Command\ListCommand;
use Psy\Command\ShowCommand;
use Psy\Configuration;
use Psy\Context;
use Psy\ContextAware;
use Psy\TabCompletion\Matcher;
class AutoCompleterTest extends \PHPUnit_Framework_TestCase
{
    public function testClassesCompletion($line, $mustContain, $mustNotContain)
    {
        $context = new Context();
        $commands = array(
            new ShowCommand(),
            new ListCommand(),
        );
        $matchers = array(
            new Matcher\VariablesMatcher(),
            new Matcher\ClassNamesMatcher(),
            new Matcher\ConstantsMatcher(),
            new Matcher\FunctionsMatcher(),
            new Matcher\ObjectMethodsMatcher(),
            new Matcher\ObjectAttributesMatcher(),
            new Matcher\KeywordsMatcher(),
            new Matcher\ClassAttributesMatcher(),
            new Matcher\ClassMethodsMatcher(),
            new Matcher\CommandsMatcher($commands),
        );
        $config = new Configuration();
        $tabCompletion = $config->getAutoCompleter();
        foreach ($matchers as $matcher) {
            if ($matcher instanceof ContextAware) {
                $matcher->setContext($context);
            }
            $tabCompletion->addMatcher($matcher);
        }
        $context->setAll(array('foo' => 12, 'bar' => new \DOMDocument()));
        $code = $tabCompletion->processCallback('', 0, array(
           'line_buffer' => $line,
           'point'       => 0,
           'end'         => strlen($line),
        ));
        foreach ($mustContain as $mc) {
            $this->assertContains($mc, $code);
        }
        foreach ($mustNotContain as $mnc) {
            $this->assertNotContains($mnc, $code);
        }
    }
    public function classesInput()
    {
        return array(
            array('T_OPE', array('T_OPEN_TAG'), array()),
            array('st', array('stdClass'), array()),
            array('stdCla', array('stdClass'), array()),
            array('new s', array('stdClass'), array()),
            array(
                'new ',
                array('stdClass', 'Psy\\Context', 'Psy\\Configuration'),
                array('require', 'array_search', 'T_OPEN_TAG', '$foo'),
            ),
            array('new Psy\\C', array('Context'), array('CASE_LOWER')),
            array('\s', array('stdClass'), array()),
            array('array_', array('array_search', 'array_map', 'array_merge'), array()),
            array('$bar->', array('load'), array()),
            array('$b', array('bar'), array()),
            array('6 + $b', array('bar'), array()),
            array('$f', array('foo'), array()),
            array('l', array('ls'), array()),
            array('ls ', array(), array('ls')),
            array('sho', array('show'), array()),
            array('12 + clone $', array('foo'), array()),
            array('$', array('foo', 'bar'), array('require', 'array_search', 'T_OPEN_TAG', 'Psy')),
            array(
                'Psy\\',
                array('Context', 'TabCompletion\\Matcher\\AbstractMatcher'),
                array('require', 'array_search'),
            ),
            array(
                'Psy\Test\TabCompletion\StaticSample::CO',
                array('Psy\Test\TabCompletion\StaticSample::CONSTANT_VALUE'),
                array(),
            ),
            array(
                'Psy\Test\TabCompletion\StaticSample::',
                array('Psy\Test\TabCompletion\StaticSample::$staticVariable'),
                array(),
            ),
            array(
                'Psy\Test\TabCompletion\StaticSample::',
                array('Psy\Test\TabCompletion\StaticSample::staticFunction'),
                array(),
            ),
        );
    }
}
