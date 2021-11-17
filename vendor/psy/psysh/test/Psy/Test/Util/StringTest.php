<?php
namespace Psy\Test\Util;
use Psy\Util\String;
class StringTest extends \PHPUnit_Framework_TestCase
{
    public function testUnvis($input, $expected)
    {
        $this->assertEquals($expected, String::unvis($input));
    }
    public function testUnvisProvider()
    {
        return json_decode(file_get_contents(__DIR__ . '/../../../fixtures/unvis_fixtures.json'));
    }
}
