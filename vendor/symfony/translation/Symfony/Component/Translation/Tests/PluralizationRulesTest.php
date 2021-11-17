<?php
namespace Symfony\Component\Translation\Tests;
use Symfony\Component\Translation\PluralizationRules;
class PluralizationRulesTest extends \PHPUnit_Framework_TestCase
{
    public function testFailedLangcodes($nplural, $langCodes)
    {
        $matrix = $this->generateTestData($nplural, $langCodes);
        $this->validateMatrix($nplural, $matrix, false);
    }
    public function testLangcodes($nplural, $langCodes)
    {
        $matrix = $this->generateTestData($nplural, $langCodes);
        $this->validateMatrix($nplural, $matrix);
    }
    public function successLangcodes()
    {
        return array(
            array('1', array('ay','bo', 'cgg','dz','id', 'ja', 'jbo', 'ka','kk','km','ko','ky')),
            array('2', array('nl', 'fr', 'en', 'de', 'de_GE')),
            array('3', array('be','bs','cs','hr')),
            array('4', array('cy','mt', 'sl')),
            array('5', array()),
            array('6', array('ar')),
        );
    }
    public function failingLangcodes()
    {
        return array(
            array('1', array('fa')),
            array('2', array('jbo')),
            array('3', array('cbs')),
            array('4', array('gd','kw')),
            array('5', array('ga')),
            array('6', array()),
        );
    }
    protected function validateMatrix($nplural, $matrix, $expectSuccess = true)
    {
        foreach ($matrix as $langCode => $data) {
            $indexes = array_flip($data);
            if ($expectSuccess) {
                $this->assertEquals($nplural, count($indexes), "Langcode '$langCode' has '$nplural' plural forms.");
            } else {
                $this->assertNotEquals((int) $nplural, count($indexes), "Langcode '$langCode' has '$nplural' plural forms.");
            }
        }
    }
    protected function generateTestData($plural, $langCodes)
    {
        $matrix = array();
        foreach ($langCodes as $langCode) {
            for ($count = 0; $count < 200; $count++) {
                $plural = PluralizationRules::get($count, $langCode);
                $matrix[$langCode][$count] = $plural;
            }
        }
        return $matrix;
    }
}
