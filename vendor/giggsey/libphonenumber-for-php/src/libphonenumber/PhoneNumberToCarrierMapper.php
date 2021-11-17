<?php
namespace libphonenumber;
use libphonenumber\prefixmapper\PrefixFileReader;
class PhoneNumberToCarrierMapper
{
    private static $instance = array();
    const MAPPING_DATA_DIRECTORY = '/carrier/data/';
    private $phoneUtil;
    private $prefixFileReader;
    private function __construct($phonePrefixDataDirectory)
    {
        if(!extension_loaded('intl')) {
            throw new \RuntimeException('The intl extension must be installed');
        }
        $this->prefixFileReader = new PrefixFileReader(dirname(__FILE__) . $phonePrefixDataDirectory);
        $this->phoneUtil = PhoneNumberUtil::getInstance();
    }
    public static function getInstance($mappingDir = self::MAPPING_DATA_DIRECTORY)
    {
        if (!array_key_exists($mappingDir, self::$instance)) {
            self::$instance[$mappingDir] = new self($mappingDir);
        }
        return self::$instance[$mappingDir];
    }
    public function getNameForValidNumber(PhoneNumber $number, $languageCode)
    {
        $languageStr = \Locale::getPrimaryLanguage($languageCode);
        $scriptStr = "";
        $regionStr = \Locale::getRegion($languageCode);
        return $this->prefixFileReader->getDescriptionForNumber($number, $languageStr, $scriptStr, $regionStr);
    }
    public function getNameForNumber(PhoneNumber $number, $languageCode)
    {
        $numberType = $this->phoneUtil->getNumberType($number);
        if ($this->isMobile($numberType)) {
            return $this->getNameForValidNumber($number, $languageCode);
        }
        return "";
    }
    public function getSafeDisplayName(PhoneNumber $number, $languageCode)
    {
        if ($this->phoneUtil->isMobileNumberPortableRegion($this->phoneUtil->getRegionCodeForNumber($number))) {
            return "";
        }
        return $this->getNameForNumber($number, $languageCode);
    }
    private function isMobile($numberType)
    {
        return ($numberType === PhoneNumberType::MOBILE ||
            $numberType === PhoneNumberType::FIXED_LINE_OR_MOBILE ||
            $numberType === PhoneNumberType::PAGER
        );
    }
}
