<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\ValidClassNamePass;
use Psy\Exception\Exception;
class ValidClassNamePassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->setPass(new ValidClassNamePass());
    }
    public function testProcessInvalid($code, $php54 = false)
    {
        try {
            $stmts = $this->parse($code);
            $this->traverse($stmts);
            $this->fail();
        } catch (Exception $e) {
            if ($php54 && version_compare(PHP_VERSION, '5.4', '<')) {
                $this->assertInstanceOf('Psy\Exception\ParseErrorException', $e);
            } else {
                $this->assertInstanceOf('Psy\Exception\FatalErrorException', $e);
            }
        }
    }
    public function getInvalid()
    {
        return array(
            array('class stdClass {}'),
            array('class stdClass {}'),
            array('interface stdClass {}'),
            array('trait stdClass {}', true),
            array("
                class Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
                class Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
            "),
            array("
                class Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
                trait Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
            ", true),
            array("
                trait Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
                class Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
            ", true),
            array("
                trait Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
                interface Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
            ", true),
            array("
                interface Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
                trait Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
            ", true),
            array("
                interface Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
                class Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
            "),
            array("
                class Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
                interface Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
            "),
            array("
                namespace Psy\\Test\\CodeCleaner {
                    class ValidClassNamePassTest {}
                }
            "),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    class Beta {}
                }
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    class Beta {}
                }
            "),
            array('class ValidClassNamePassTest extends NotAClass {}'),
            array('class ValidClassNamePassTest extends ArrayAccess {}'),
            array('class ValidClassNamePassTest implements stdClass {}'),
            array('class ValidClassNamePassTest implements ArrayAccess, stdClass {}'),
            array('interface ValidClassNamePassTest extends stdClass {}'),
            array('interface ValidClassNamePassTest extends ArrayAccess, stdClass {}'),
            array('new Psy_Test_CodeCleaner_ValidClassNamePass_Gamma();'),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    new Psy_Test_CodeCleaner_ValidClassNamePass_Delta();
                }
            "),
            array('Psy\\Test\\CodeCleaner\\ValidClassNamePass\\NotAClass::FOO'),
            array('Psy\\Test\\CodeCleaner\\ValidClassNamePass\\NotAClass::foo()'),
            array('Psy\\Test\\CodeCleaner\\ValidClassNamePass\\NotAClass::$foo()'),
        );
    }
    public function testProcessValid($code)
    {
        $stmts = $this->parse($code);
        $this->traverse($stmts);
    }
    public function getValid()
    {
        return array(
            array('class Psy_Test_CodeCleaner_ValidClassNamePass_Epsilon {}'),
            array('namespace Psy\Test\CodeCleaner\ValidClassNamePass; class Zeta {}'),
            array("
                namespace { class Psy_Test_CodeCleaner_ValidClassNamePass_Eta {}; }
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    class Psy_Test_CodeCleaner_ValidClassNamePass_Eta {}
                }
            "),
            array('namespace Psy\Test\CodeCleaner\ValidClassNamePass { class stdClass {} }'),
            array('new stdClass();'),
            array('new stdClass();'),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    class Theta {}
                }
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    new Theta();
                }
            "),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    class Iota {}
                    new Iota();
                }
            "),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    class Kappa {}
                }
                namespace {
                    new \\Psy\\Test\\CodeCleaner\\ValidClassNamePass\\Kappa();
                }
            "),
            array('class A {} A::FOO'),
            array('$a = new DateTime; $a::ATOM'),
            array('DateTime::createFromFormat()'),
            array('DateTime::$someMethod()'),
            array('Psy\Test\CodeCleaner\Fixtures\ClassWithStatic::doStuff()'),
            array('Psy\Test\CodeCleaner\Fixtures\ClassWithCallStatic::doStuff()'),
        );
    }
}
