<?php
class PHP_CodeCoverage_UtilTest extends PHPUnit_Framework_TestCase
{
    public function testPercent()
    {
        $this->assertEquals(100, PHP_CodeCoverage_Util::percent(100, 0));
        $this->assertEquals(100, PHP_CodeCoverage_Util::percent(100, 100));
        $this->assertEquals(
          '100.00%', PHP_CodeCoverage_Util::percent(100, 100, true)
        );
    }
}
