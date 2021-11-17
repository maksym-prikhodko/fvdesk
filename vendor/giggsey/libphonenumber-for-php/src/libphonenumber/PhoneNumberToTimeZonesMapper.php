<?php
namespace libphonenumber;
use libphonenumber\prefixmapper\PrefixTimeZonesMap;
class PhoneNumberToTimeZonesMapper
{
    const UNKNOWN_TIMEZONE = 'Etc/Unknown';
    const MAPPING_DATA_DIRECTORY = '/timezone/data/';
    const MAPPING_DATA_FILE_NAME = "map_data.php";
    private static $instance = null;
    private $unknownTimeZoneList = array();
    private $phoneUtil;
    private $prefixTimeZonesMap;
    private function __construct($phonePrefixDataDirectory)
    {
        $this->prefixTimeZonesMap = self::loadPrefixTimeZonesMapFromFile(
            dirname(__FILE__) . $phonePrefixDataDirectory . DIRECTORY_SEPARATOR . self::MAPPING_DATA_FILE_NAME
        );
        $this->phoneUtil = PhoneNumberUtil::getInstance();
        $this->unknownTimeZoneList[] = self::UNKNOWN_TIMEZONE;
    }
    private static function loadPrefixTimeZonesMapFromFile($path)
    {
        if (!is_readable($path)) {
            throw new \InvalidArgumentException("Mapping file can not be found");
        }
        $data = require $path;
        $map = new PrefixTimeZonesMap($data);
        return $map;
    }
    public static function getInstance($mappingDir = self::MAPPING_DATA_DIRECTORY)
    {
        if (self::$instance === null) {
            self::$instance = new self($mappingDir);
        }
        return self::$instance;
    }
    public static function getUnknownTimeZone()
    {
        return self::UNKNOWN_TIMEZONE;
    }
    public function getTimeZonesForNumber(PhoneNumber $number)
    {
        $numberType = $this->phoneUtil->getNumberType($number);
        if ($numberType === PhoneNumberType::UNKNOWN) {
            return $this->unknownTimeZoneList;
        } elseif (!$this->canBeGeocoded($numberType)) {
            return $this->getCountryLevelTimeZonesforNumber($number);
        }
        return $this->getTimeZonesForGeographicalNumber($number);
    }
    public function canBeGeocoded($numberType)
    {
        return ($numberType === PhoneNumberType::FIXED_LINE ||
            $numberType === PhoneNumberType::MOBILE ||
            $numberType === PhoneNumberType::FIXED_LINE_OR_MOBILE
        );
    }
    private function getCountryLevelTimeZonesforNumber(PhoneNumber $number)
    {
        $timezones = $this->prefixTimeZonesMap->lookupCountryLevelTimeZonesForNumber($number);
        return (count($timezones) == 0) ? $this->unknownTimeZoneList : $timezones;
    }
    public function getTimeZonesForGeographicalNumber(PhoneNumber $number)
    {
        return $this->getTimeZonesForGeocodableNumber($number);
    }
    private function getTimeZonesForGeocodableNumber(PhoneNumber $number)
    {
        $timezones = $this->prefixTimeZonesMap->lookupTimeZonesForNumber($number);
        return (count($timezones) == 0) ? $this->unknownTimeZoneList : $timezones;
    }
}
