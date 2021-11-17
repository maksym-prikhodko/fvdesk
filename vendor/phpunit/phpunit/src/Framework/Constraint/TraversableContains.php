<?php
class PHPUnit_Framework_Constraint_TraversableContains extends PHPUnit_Framework_Constraint
{
    protected $checkForObjectIdentity;
    protected $checkForNonObjectIdentity;
    protected $value;
    public function __construct($value, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
    {
        parent::__construct();
        if (!is_bool($checkForObjectIdentity)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'boolean');
        }
        if (!is_bool($checkForNonObjectIdentity)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(3, 'boolean');
        }
        $this->checkForObjectIdentity    = $checkForObjectIdentity;
        $this->checkForNonObjectIdentity = $checkForNonObjectIdentity;
        $this->value                     = $value;
    }
    protected function matches($other)
    {
        if ($other instanceof SplObjectStorage) {
            return $other->contains($this->value);
        }
        if (is_object($this->value)) {
            foreach ($other as $element) {
                if (($this->checkForObjectIdentity &&
                     $element === $this->value) ||
                    (!$this->checkForObjectIdentity &&
                     $element == $this->value)) {
                    return true;
                }
            }
        } else {
            foreach ($other as $element) {
                if (($this->checkForNonObjectIdentity &&
                     $element === $this->value) ||
                    (!$this->checkForNonObjectIdentity &&
                     $element == $this->value)) {
                    return true;
                }
            }
        }
        return false;
    }
    public function toString()
    {
        if (is_string($this->value) && strpos($this->value, "\n") !== false) {
            return 'contains "' . $this->value . '"';
        } else {
            return 'contains ' . $this->exporter->export($this->value);
        }
    }
    protected function failureDescription($other)
    {
        return sprintf(
            '%s %s',
            is_array($other) ? 'an array' : 'a traversable',
            $this->toString()
        );
    }
}
