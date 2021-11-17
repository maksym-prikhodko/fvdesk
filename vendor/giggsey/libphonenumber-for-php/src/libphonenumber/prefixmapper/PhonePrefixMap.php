<?php
namespace libphonenumber\prefixmapper;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
class PhonePrefixMap
{
    private $phonePrefixMapStorage = array();
    private $phoneUtil;
    public function __construct($map)
    {
        $this->phonePrefixMapStorage = $map;
        $this->phoneUtil = PhoneNumberUtil::getInstance();
    }
    public function lookup(PhoneNumber $number)
    {
        $phonePrefix = $number->getCountryCode() . $this->phoneUtil->getNationalSignificantNumber($number);
        return $this->lookupKey($phonePrefix);
    }
    public function lookupKey($key)
    {
        if (count($this->phonePrefixMapStorage) == 0) {
            return null;
        }
        while (strlen($key) > 0) {
            if (array_key_exists($key, $this->phonePrefixMapStorage)) {
                return $this->phonePrefixMapStorage[$key];
            }
            $key = substr($key, 0, -1);
        }
        return null;
    }
}
