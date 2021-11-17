<?php
class PHPUnit_Framework_Constraint_Attribute extends PHPUnit_Framework_Constraint_Composite
{
    protected $attributeName;
    public function __construct(PHPUnit_Framework_Constraint $constraint, $attributeName)
    {
        parent::__construct($constraint);
        $this->attributeName = $attributeName;
    }
    public function evaluate($other, $description = '', $returnResult = false)
    {
        return parent::evaluate(
            PHPUnit_Framework_Assert::readAttribute(
                $other,
                $this->attributeName
            ),
            $description,
            $returnResult
        );
    }
    public function toString()
    {
        return 'attribute "' . $this->attributeName . '" ' .
               $this->innerConstraint->toString();
    }
    protected function failureDescription($other)
    {
        return $this->toString();
    }
}
