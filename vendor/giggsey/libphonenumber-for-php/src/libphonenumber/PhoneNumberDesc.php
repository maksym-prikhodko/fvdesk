<?php
namespace libphonenumber;
class PhoneNumberDesc
{
    private $hasNationalNumberPattern = false;
    private $nationalNumberPattern = "";
    private $hasPossibleNumberPattern = false;
    private $possibleNumberPattern = "";
    private $hasExampleNumber = false;
    private $exampleNumber = "";
    public function hasNationalNumberPattern()
    {
        return $this->hasNationalNumberPattern;
    }
    public function getNationalNumberPattern()
    {
        return $this->nationalNumberPattern;
    }
    public function setNationalNumberPattern($value)
    {
        $this->hasNationalNumberPattern = true;
        $this->nationalNumberPattern = $value;
        return $this;
    }
    public function hasPossibleNumberPattern()
    {
        return $this->hasPossibleNumberPattern;
    }
    public function getPossibleNumberPattern()
    {
        return $this->possibleNumberPattern;
    }
    public function setPossibleNumberPattern($value)
    {
        $this->hasPossibleNumberPattern = true;
        $this->possibleNumberPattern = $value;
        return $this;
    }
    public function hasExampleNumber()
    {
        return $this->hasExampleNumber;
    }
    public function getExampleNumber()
    {
        return $this->exampleNumber;
    }
    public function setExampleNumber($value)
    {
        $this->hasExampleNumber = true;
        $this->exampleNumber = $value;
        return $this;
    }
    public function mergeFrom(PhoneNumberDesc $other)
    {
        if ($other->hasNationalNumberPattern()) {
            $this->setNationalNumberPattern($other->getNationalNumberPattern());
        }
        if ($other->hasPossibleNumberPattern()) {
            $this->setPossibleNumberPattern($other->getPossibleNumberPattern());
        }
        if ($other->hasExampleNumber()) {
            $this->setExampleNumber($other->getExampleNumber());
        }
        return $this;
    }
    public function exactlySameAs(PhoneNumberDesc $other)
    {
        return $this->nationalNumberPattern === $other->nationalNumberPattern &&
        $this->possibleNumberPattern === $other->possibleNumberPattern &&
        $this->exampleNumber === $other->exampleNumber;
    }
    public function toArray()
    {
        $data = array();
        if ($this->hasNationalNumberPattern()) {
            $data['NationalNumberPattern'] = $this->getNationalNumberPattern();
        }
        if ($this->hasPossibleNumberPattern()) {
            $data['PossibleNumberPattern'] = $this->getPossibleNumberPattern();
        }
        if ($this->hasExampleNumber()) {
            $data['ExampleNumber'] = $this->getExampleNumber();
        }
        return $data;
    }
    public function fromArray(array $input)
    {
        if (isset($input['NationalNumberPattern']) && $input['NationalNumberPattern'] != '') {
            $this->setNationalNumberPattern($input['NationalNumberPattern']);
        }
        if (isset($input['PossibleNumberPattern']) && $input['NationalNumberPattern'] != '') {
            $this->setPossibleNumberPattern($input['PossibleNumberPattern']);
        }
        if (isset($input['ExampleNumber']) && $input['NationalNumberPattern'] != '') {
            $this->setExampleNumber($input['ExampleNumber']);
        }
        return $this;
    }
}
