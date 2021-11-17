<?php
class PHPUnit_Framework_ExpectationFailedException extends PHPUnit_Framework_AssertionFailedError
{
    protected $comparisonFailure;
    public function __construct($message, SebastianBergmann\Comparator\ComparisonFailure $comparisonFailure = null, Exception $previous = null)
    {
        $this->comparisonFailure = $comparisonFailure;
        parent::__construct($message, 0, $previous);
    }
    public function getComparisonFailure()
    {
        return $this->comparisonFailure;
    }
}
