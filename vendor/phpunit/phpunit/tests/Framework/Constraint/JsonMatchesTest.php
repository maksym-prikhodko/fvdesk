<?php
class Framework_Constraint_JsonMatchesTest extends PHPUnit_Framework_TestCase
{
    public function testEvaluate($expected, $jsonOther, $jsonValue)
    {
        $constraint = new PHPUnit_Framework_Constraint_JsonMatches($jsonValue);
        $this->assertEquals($expected, $constraint->evaluate($jsonOther, '', true));
    }
    public function testToString()
    {
        $jsonValue = json_encode(array('Mascott' => 'Tux'));
        $constraint = new PHPUnit_Framework_Constraint_JsonMatches($jsonValue);
        $this->assertEquals('matches JSON string "' . $jsonValue . '"', $constraint->toString());
    }
    public static function evaluateDataprovider()
    {
        return array(
            'valid JSON' => array(true, json_encode(array('Mascott' => 'Tux')), json_encode(array('Mascott' => 'Tux'))),
            'error syntax' => array(false, '{"Mascott"::}', json_encode(array('Mascott' => 'Tux'))),
            'error UTF-8' => array(false, json_encode('\xB1\x31'), json_encode(array('Mascott' => 'Tux'))),
            'invalid JSON in class instantiation' => array(false, json_encode(array('Mascott' => 'Tux')), '{"Mascott"::}'),
        );
    }
}
