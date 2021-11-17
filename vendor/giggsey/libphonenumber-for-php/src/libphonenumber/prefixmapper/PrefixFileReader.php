<?php
namespace libphonenumber\prefixmapper;
use libphonenumber\PhoneNumber;
class PrefixFileReader
{
    private $phonePrefixDataDirectory;
    private $mappingFileProvider;
    private $availablePhonePrefixMaps = array();
    public function __construct($phonePrefixDataDirectory)
    {
        $this->phonePrefixDataDirectory = $phonePrefixDataDirectory;
        $this->loadMappingFileProvider();
    }
    private function loadMappingFileProvider()
    {
        $mapPath = $this->phonePrefixDataDirectory . DIRECTORY_SEPARATOR . "Map.php";
        if (!file_exists($mapPath)) {
            throw new \InvalidArgumentException("Invalid data directory");
        }
        $map = require $mapPath;
        $this->mappingFileProvider = new MappingFileProvider($map);
    }
    public function getPhonePrefixDescriptions($prefixMapKey, $language, $script, $region)
    {
        $fileName = $this->mappingFileProvider->getFileName($prefixMapKey, $language, $script, $region);
        if (strlen($fileName) == 0) {
            return null;
        }
        if (!in_array($fileName, $this->availablePhonePrefixMaps)) {
            $this->loadPhonePrefixMapFromFile($fileName);
        }
        return $this->availablePhonePrefixMaps[$fileName];
    }
    private function loadPhonePrefixMapFromFile($fileName)
    {
        $path = $this->phonePrefixDataDirectory . DIRECTORY_SEPARATOR . $fileName;
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Data does not exist");
        }
        $map = require $path;
        $areaCodeMap = new PhonePrefixMap($map);
        $this->availablePhonePrefixMaps[$fileName] = $areaCodeMap;
    }
    public function mayFallBackToEnglish($language)
    {
        return ($language != 'zh' && $language != 'ja' && $language != 'ko');
    }
    public function getDescriptionForNumber(PhoneNumber $number, $language, $script, $region)
    {
        $countryCallingCode = $number->getCountryCode();
        if ($countryCallingCode === 1) {
            $phonePrefix = (1000 + intval($number->getNationalNumber() / 10000000));
        } elseif ($countryCallingCode === 86) {
            $phonePrefix = '86' . substr($number->getNationalNumber(), 0, 3);
        } else {
            $phonePrefix = $countryCallingCode;
        }
        $phonePrefixDescriptions = $this->getPhonePrefixDescriptions($phonePrefix, $language, $script, $region);
        $description = ($phonePrefixDescriptions !== null) ? $phonePrefixDescriptions->lookup($number) : null;
        if (($description === null || strlen($description) === 0) && $this->mayFallBackToEnglish($language)) {
            $defaultMap = $this->getPhonePrefixDescriptions($phonePrefix, "en", "", "");
            if ($defaultMap === null) {
                return "";
            }
            $description = $defaultMap->lookup($number);
        }
        return ($description !== null) ? $description : "";
    }
}
