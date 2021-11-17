<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\ValidFunctionNamePass;
class ValidFunctionNamePassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->setPass(new ValidFunctionNamePass());
    }
    public function testProcessInvalidFunctionCallsAndDeclarations($code)
    {
        $stmts = $this->parse($code);
        $this->traverse($stmts);
    }
    public function getInvalidFunctions()
    {
        return array(
            array('function array_merge() {}'),
            array('function Array_Merge() {}'),
            array("
                function psy_test_codecleaner_validfunctionnamepass_alpha() {}
                function psy_test_codecleaner_validfunctionnamepass_alpha() {}
            "),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidFunctionNamePass {
                    function beta() {}
                }
                namespace Psy\\Test\\CodeCleaner\\ValidFunctionNamePass {
                    function beta() {}
                }
            "),
            array('psy_test_codecleaner_validfunctionnamepass_gamma()'),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidFunctionNamePass {
                    delta();
                }
            "),
        );
    }
    public function testProcessValidFunctionCallsAndDeclarations($code)
    {
        $stmts = $this->parse($code);
        $this->traverse($stmts);
    }
    public function getValidFunctions()
    {
        return array(
            array('function psy_test_codecleaner_validfunctionnamepass_epsilon() {}'),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidFunctionNamePass {
                    function zeta() {}
                }
            "),
            array("
                namespace {
                    function psy_test_codecleaner_validfunctionnamepass_eta() {}
                }
                namespace Psy\\Test\\CodeCleaner\\ValidFunctionNamePass {
                    function psy_test_codecleaner_validfunctionnamepass_eta() {}
                }
            "),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidFunctionNamePass {
                    function psy_test_codecleaner_validfunctionnamepass_eta() {}
                }
                namespace {
                    function psy_test_codecleaner_validfunctionnamepass_eta() {}
                }
            "),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidFunctionNamePass {
                    function array_merge() {}
                }
            "),
            array('array_merge();'),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidFunctionNamePass {
                    function theta() {}
                }
                namespace Psy\\Test\\CodeCleaner\\ValidFunctionNamePass {
                    theta();
                }
            "),
            array('$test = function(){};$test()'),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidFunctionNamePass {
                    function theta() {}
                }
                namespace {
                    Psy\\Test\\CodeCleaner\\ValidFunctionNamePass\\theta();
                }
            "),
        );
    }
}
