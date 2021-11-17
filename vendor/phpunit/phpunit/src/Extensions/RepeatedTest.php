<?php
class PHPUnit_Extensions_RepeatedTest extends PHPUnit_Extensions_TestDecorator
{
    protected $filter = false;
    protected $groups = array();
    protected $excludeGroups = array();
    protected $processIsolation = false;
    protected $timesRepeat = 1;
    public function __construct(PHPUnit_Framework_Test $test, $timesRepeat = 1, $processIsolation = false)
    {
        parent::__construct($test);
        if (is_integer($timesRepeat) &&
            $timesRepeat >= 0) {
            $this->timesRepeat = $timesRepeat;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                2,
                'positive integer'
            );
        }
        $this->processIsolation = $processIsolation;
    }
    public function count()
    {
        return $this->timesRepeat * count($this->test);
    }
    public function run(PHPUnit_Framework_TestResult $result = null)
    {
        if ($result === null) {
            $result = $this->createResult();
        }
        for ($i = 0; $i < $this->timesRepeat && !$result->shouldStop(); $i++) {
            if ($this->test instanceof PHPUnit_Framework_TestSuite) {
                $this->test->setRunTestInSeparateProcess($this->processIsolation);
            }
            $this->test->run($result);
        }
        return $result;
    }
}
