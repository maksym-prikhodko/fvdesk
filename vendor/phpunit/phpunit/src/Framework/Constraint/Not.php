<?php
class PHPUnit_Framework_Constraint_Not extends PHPUnit_Framework_Constraint
{
    protected $constraint;
    public function __construct($constraint)
    {
        parent::__construct();
        if (!($constraint instanceof PHPUnit_Framework_Constraint)) {
            $constraint = new PHPUnit_Framework_Constraint_IsEqual($constraint);
        }
        $this->constraint = $constraint;
    }
    public static function negate($string)
    {
        return str_replace(
            array(
            'contains ',
            'exists',
            'has ',
            'is ',
            'are ',
            'matches ',
            'starts with ',
            'ends with ',
            'reference ',
            'not not '
            ),
            array(
            'does not contain ',
            'does not exist',
            'does not have ',
            'is not ',
            'are not ',
            'does not match ',
            'starts not with ',
            'ends not with ',
            'don\'t reference ',
            'not '
            ),
            $string
        );
    }
    public function evaluate($other, $description = '', $returnResult = false)
    {
        $success = !$this->constraint->evaluate($other, $description, true);
        if ($returnResult) {
            return $success;
        }
        if (!$success) {
            $this->fail($other, $description);
        }
    }
    protected function failureDescription($other)
    {
        switch (get_class($this->constraint)) {
            case 'PHPUnit_Framework_Constraint_And':
            case 'PHPUnit_Framework_Constraint_Not':
            case 'PHPUnit_Framework_Constraint_Or': {
                return 'not( ' . $this->constraint->failureDescription($other) . ' )';
                }
            break;
            default: {
                return self::negate(
                    $this->constraint->failureDescription($other)
                );
                }
        }
    }
    public function toString()
    {
        switch (get_class($this->constraint)) {
            case 'PHPUnit_Framework_Constraint_And':
            case 'PHPUnit_Framework_Constraint_Not':
            case 'PHPUnit_Framework_Constraint_Or': {
                return 'not( ' . $this->constraint->toString() . ' )';
                }
            break;
            default: {
                return self::negate(
                    $this->constraint->toString()
                );
                }
        }
    }
    public function count()
    {
        return count($this->constraint);
    }
}
