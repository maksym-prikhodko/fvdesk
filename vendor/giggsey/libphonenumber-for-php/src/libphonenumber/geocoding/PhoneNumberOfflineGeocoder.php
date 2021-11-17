<?php
namespace libphonenumber\geocoding;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\prefixmapper\PrefixFileReader;
class PhoneNumberOfflineGeocoder
{
    const MAPPING_DATA_DIRECTORY = '/data';
    private static $instance;
    private $phoneUtil;
    private $prefixFileReader = null;
    private function __construct($phonePrefixDataDirectory)
    {
        if(!extension_loaded('intl')) {
            throw new \RuntimeException('The intl extension must be installed');
        }
        $this->phoneUtil = PhoneNumberUtil::getInstance();
        $this->prefixFileReader = new PrefixFileReader(dirname(__FILE__) . $phonePrefixDataDirectory);
    }
    public static function getInstance($mappingDir = self::MAPPING_DATA_DIRECTORY)
    {
        if (self::$instance === null) {
            self::$instance = new self($mappingDir);
        }
        return self::$instance;
    }
    public static function resetInstance()
    {
        self::$instance = null;
    }
    public function getDescriptionForNumber(PhoneNumber $number, $locale, $userRegion = null)
    {
        $numberType = $this->phoneUtil->getNumberType($number);
        if ($numberType === PhoneNumberType::UNKNOWN) {
            return "";
        } elseif (!$this->canBeGeocoded($numberType)) {
            return $this->getCountryNameForNumber($number, $locale);
        }
        return $this->getDescriptionForValidNumber($number, $locale, $userRegion);
    }
    private function canBeGeocoded($numberType)
    {
        return ($numberType === PhoneNumberType::FIXED_LINE || $numberType === PhoneNumberType::MOBILE || $numberType === PhoneNumberType::FIXED_LINE_OR_MOBILE);
    }
    private function getCountryNameForNumber(PhoneNumber $number, $locale)
    {
        $regionCodes = $this->phoneUtil->getRegionCodesForCountryCode($number->getCountryCode());
        if (count($regionCodes) === 1) {
            return $this->getRegionDisplayName($regionCodes[0], $locale);
        } else {
            $regionWhereNumberIsValid = 'ZZ';
            foreach ($regionCodes as $regionCode) {
                if ($this->phoneUtil->isValidNumberForRegion($number, $regionCode)) {
                    if ($regionWhereNumberIsValid !== 'ZZ') {
                        return "";
                    }
                    $regionWhereNumberIsValid = $regionCode;
                }
            }
            return $this->getRegionDisplayName($regionWhereNumberIsValid, $locale);
        }
    }
    private function getRegionDisplayName($regionCode, $locale)
    {
        if ($regionCode === null || $regionCode == 'ZZ' || $regionCode === PhoneNumberUtil::REGION_CODE_FOR_NON_GEO_ENTITY) {
            return "";
        }
        return Locale::getDisplayRegion(
            Locale::countryCodeToLocale($regionCode),
            $locale
        );
    }
    public function getDescriptionForValidNumber(PhoneNumber $number, $locale, $userRegion = null)
    {
        $regionCode = $this->phoneUtil->getRegionCodeForNumber($number);
        if ($userRegion == null || $userRegion == $regionCode) {
            $languageStr = Locale::getPrimaryLanguage($locale);
            $scriptStr = "";
            $regionStr = Locale::getRegion($locale);
            $mobileToken = $this->phoneUtil->getCountryMobileToken($number->getCountryCode());
            $nationalNumber = $this->phoneUtil->getNationalSignificantNumber($number);
            if ($mobileToken !== "" && (!strncmp($nationalNumber, $mobileToken, strlen($mobileToken)))) {
                $nationalNumber = substr($nationalNumber, strlen($mobileToken));
                $region = $this->phoneUtil->getRegionCodeForCountryCode($number->getCountryCode());
                try {
                    $copiedNumber = $this->phoneUtil->parse($nationalNumber, $region);
                } catch (NumberParseException $e) {
                    $copiedNumber = $number;
                }
                $areaDescription = $this->prefixFileReader->getDescriptionForNumber($copiedNumber, $languageStr, $scriptStr, $regionStr);
            } else {
                $areaDescription = $this->prefixFileReader->getDescriptionForNumber($number, $languageStr, $scriptStr, $regionStr);
            }
            return (strlen($areaDescription) > 0) ? $areaDescription : $this->getCountryNameForNumber($number, $locale);
        }
        return $this->getRegionDisplayName($regionCode, $locale);
    }
}
