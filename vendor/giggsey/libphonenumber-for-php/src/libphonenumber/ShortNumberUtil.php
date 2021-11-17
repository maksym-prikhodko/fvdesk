<?php
namespace libphonenumber;
class ShortNumberUtil
{
    private $phoneUtil;
    public function __construct(PhoneNumberUtil $phoneNumberUtil = null)
    {
        $this->phoneUtil = $phoneNumberUtil;
    }
    public function getSupportedRegions()
    {
        return ShortNumberInfo::getInstance($this->phoneUtil)->getSupportedRegions();
    }
    public function connectsToEmergencyNumber($number, $regionCode)
    {
        return ShortNumberInfo::getInstance($this->phoneUtil)->connectsToEmergencyNumber($number, $regionCode);
    }
    public function isEmergencyNumber($number, $regionCode)
    {
        return ShortNumberInfo::getInstance($this->phoneUtil)->isEmergencyNumber($number, $regionCode);
    }
}
