<?php
namespace libphonenumber;
class ShortNumberInfo
{
    const META_DATA_FILE_PREFIX = 'ShortNumberMetadata';
    private static $instance = null;
    private $matcherAPI;
    private $currentFilePrefix;
    private $regionToMetadataMap = array();
    private $countryCallingCodeToRegionCodeMap = array();
    private $countryCodeToNonGeographicalMetadataMap = array();
    private static $regionsWhereEmergencyNumbersMustBeExact = array(
        'BR',
        'CL',
        'NI',
    );
    private function __construct(MatcherAPIInterface $matcherAPI)
    {
        $this->matcherAPI = $matcherAPI;
        $this->countryCallingCodeToRegionCodeMap = CountryCodeToRegionCodeMap::$countryCodeToRegionCodeMap;
        $this->currentFilePrefix = dirname(__FILE__) . '/data/' . self::META_DATA_FILE_PREFIX;
    }
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self(RegexBasedMatcher::create());
        }
        return self::$instance;
    }
    public static function resetInstance()
    {
        self::$instance = null;
    }
    private function getRegionCodesForCountryCode($countryCallingCode)
    {
        if (!array_key_exists($countryCallingCode, $this->countryCallingCodeToRegionCodeMap)) {
            $regionCodes = null;
        } else {
            $regionCodes = $this->countryCallingCodeToRegionCodeMap[$countryCallingCode];
        }
        return ($regionCodes === null) ? array() : $regionCodes;
    }
    public function getSupportedRegions()
    {
        return ShortNumbersRegionCodeSet::$shortNumbersRegionCodeSet;
    }
    public function getExampleShortNumber($regionCode)
    {
        $phoneMetadata = $this->getMetadataForRegion($regionCode);
        if ($phoneMetadata === null) {
            return "";
        }
        $desc = $phoneMetadata->getShortCode();
        if ($desc !== null && $desc->hasExampleNumber()) {
            return $desc->getExampleNumber();
        }
        return "";
    }
    public function getMetadataForRegion($regionCode)
    {
        if (!in_array($regionCode, ShortNumbersRegionCodeSet::$shortNumbersRegionCodeSet)) {
            return null;
        }
        if (!isset($this->regionToMetadataMap[$regionCode])) {
            $this->loadMetadataFromFile($this->currentFilePrefix, $regionCode, 0);
        }
        return isset($this->regionToMetadataMap[$regionCode]) ? $this->regionToMetadataMap[$regionCode] : null;
    }
    private function loadMetadataFromFile($filePrefix, $regionCode, $countryCallingCode)
    {
        $isNonGeoRegion = PhoneNumberUtil::REGION_CODE_FOR_NON_GEO_ENTITY === $regionCode;
        $fileName = $filePrefix . '_' . ($isNonGeoRegion ? $countryCallingCode : $regionCode) . '.php';
        if (!is_readable($fileName)) {
            throw new \Exception('missing metadata: ' . $fileName);
        } else {
            $data = include $fileName;
            $metadata = new PhoneMetadata();
            $metadata->fromArray($data);
            if ($isNonGeoRegion) {
                $this->countryCodeToNonGeographicalMetadataMap[$countryCallingCode] = $metadata;
            } else {
                $this->regionToMetadataMap[$regionCode] = $metadata;
            }
        }
    }
    public function getExampleShortNumberForCost($regionCode, $cost)
    {
        $phoneMetadata = $this->getMetadataForRegion($regionCode);
        if ($phoneMetadata === null) {
            return "";
        }
        $desc = null;
        switch ($cost) {
            case ShortNumberCost::TOLL_FREE:
                $desc = $phoneMetadata->getTollFree();
                break;
            case ShortNumberCost::STANDARD_RATE:
                $desc = $phoneMetadata->getStandardRate();
                break;
            case ShortNumberCost::PREMIUM_RATE:
                $desc = $phoneMetadata->getPremiumRate();
                break;
            default:
                break;
        }
        if ($desc !== null && $desc->hasExampleNumber()) {
            return $desc->getExampleNumber();
        }
        return "";
    }
    public function connectsToEmergencyNumber($number, $regionCode)
    {
        return $this->matchesEmergencyNumberHelper($number, $regionCode, true );
    }
    private function matchesEmergencyNumberHelper($number, $regionCode, $allowPrefixMatch)
    {
        $number = PhoneNumberUtil::extractPossibleNumber($number);
        $matcher = new Matcher(PhoneNumberUtil::$PLUS_CHARS_PATTERN, $number);
        if ($matcher->lookingAt()) {
            return false;
        }
        $metadata = $this->getMetadataForRegion($regionCode);
        if ($metadata === null || !$metadata->hasEmergency()) {
            return false;
        }
        $normalizedNumber = PhoneNumberUtil::normalizeDigitsOnly($number);
        $emergencyDesc = $metadata->getEmergency();
        $allowPrefixMatchForRegion = ($allowPrefixMatch
            && !in_array($regionCode, self::$regionsWhereEmergencyNumbersMustBeExact)
        );
        return $this->matcherAPI->matchesNationalNumber($normalizedNumber, $emergencyDesc, $allowPrefixMatchForRegion);
    }
    public function isCarrierSpecific(PhoneNumber $number)
    {
        $regionCodes = $this->getRegionCodesForCountryCode($number->getCountryCode());
        $regionCode = $this->getRegionCodeForShortNumberFromRegionList($number, $regionCodes);
        $nationalNumber = $this->getNationalSignificantNumber($number);
        $phoneMetadata = $this->getMetadataForRegion($regionCode);
        return ($phoneMetadata !== null) && ($this->matchesPossibleNumberAndNationalNumber(
            $nationalNumber,
            $phoneMetadata->getCarrierSpecific()
        ));
    }
    private function getRegionCodeForShortNumberFromRegionList(PhoneNumber $number, $regionCodes)
    {
        if (count($regionCodes) == 0) {
            return null;
        } elseif (count($regionCodes) == 1) {
            return $regionCodes[0];
        }
        $nationalNumber = $this->getNationalSignificantNumber($number);
        foreach ($regionCodes as $regionCode) {
            $phoneMetadata = $this->getMetadataForRegion($regionCode);
            if ($phoneMetadata !== null
                && $this->matchesPossibleNumberAndNationalNumber($nationalNumber, $phoneMetadata->getShortCode())
            ) {
                return $regionCode;
            }
        }
        return null;
    }
    public function isPossibleShortNumber(PhoneNumber $number)
    {
        $regionCodes = $this->getRegionCodesForCountryCode($number->getCountryCode());
        $shortNumber = $this->getNationalSignificantNumber($number);
        foreach ($regionCodes as $region) {
            $phoneMetadata = $this->getMetadataForRegion($region);
            if ($this->matcherAPI->matchesPossibleNumber($shortNumber, $phoneMetadata->getGeneralDesc())) {
                return true;
            }
        }
        return false;
    }
    public function isPossibleShortNumberForRegion($shortNumber, $regionDialingFrom)
    {
        $phoneMetadata = $this->getMetadataForRegion($regionDialingFrom);
        if ($phoneMetadata === null) {
            return false;
        }
        if ($shortNumber instanceof PhoneNumber) {
            return $this->matcherAPI->matchesPossibleNumber(
                $this->getNationalSignificantNumber($shortNumber),
                $phoneMetadata->getGeneralDesc()
            );
        } else {
            return $this->matcherAPI->matchesPossibleNumber($shortNumber, $phoneMetadata->getGeneralDesc());
        }
    }
    public function isValidShortNumber(PhoneNumber $number)
    {
        $regionCodes = $this->getRegionCodesForCountryCode($number->getCountryCode());
        $regionCode = $this->getRegionCodeForShortNumberFromRegionList($number, $regionCodes);
        if (count($regionCodes) > 1 && $regionCode !== null) {
            return true;
        }
        return $this->isValidShortNumberForRegion($number, $regionCode);
    }
    public function isValidShortNumberForRegion($number, $regionDialingFrom)
    {
        $phoneMetadata = $this->getMetadataForRegion($regionDialingFrom);
        if ($phoneMetadata === null) {
            return false;
        }
        if ($number instanceof PhoneNumber) {
            $shortNumber = $this->getNationalSignificantNumber($number);
        } else {
            $shortNumber = $number;
        }
        $generalDesc = $phoneMetadata->getGeneralDesc();
        if (!$this->matchesPossibleNumberAndNationalNumber($shortNumber, $generalDesc)) {
            return false;
        }
        $shortNumberDesc = $phoneMetadata->getShortCode();
        return $this->matchesPossibleNumberAndNationalNumber($shortNumber, $shortNumberDesc);
    }
    public function getExpectedCostForRegion($number, $regionDialingFrom)
    {
        $phoneMetadata = $this->getMetadataForRegion($regionDialingFrom);
        if ($phoneMetadata === null) {
            return ShortNumberCost::UNKNOWN_COST;
        }
        if ($number instanceof PhoneNumber) {
            $shortNumber = $this->getNationalSignificantNumber($number);
        } else {
            $shortNumber = $number;
        }
        if ($this->matchesPossibleNumberAndNationalNumber($shortNumber, $phoneMetadata->getPremiumRate())) {
            return ShortNumberCost::PREMIUM_RATE;
        }
        if ($this->matchesPossibleNumberAndNationalNumber($shortNumber, $phoneMetadata->getStandardRate())) {
            return ShortNumberCost::STANDARD_RATE;
        }
        if ($this->matchesPossibleNumberAndNationalNumber($shortNumber, $phoneMetadata->getTollFree())) {
            return ShortNumberCost::TOLL_FREE;
        }
        if ($this->isEmergencyNumber($shortNumber, $regionDialingFrom)) {
            return ShortNumberCost::TOLL_FREE;
        }
        return ShortNumberCost::UNKNOWN_COST;
    }
    public function getExpectedCost(PhoneNumber $number)
    {
        $regionCodes = $this->getRegionCodesForCountryCode($number->getCountryCode());
        if (count($regionCodes) == 0) {
            return ShortNumberCost::UNKNOWN_COST;
        }
        if (count($regionCodes) == 1) {
            return $this->getExpectedCostForRegion($number, $regionCodes[0]);
        }
        $cost = ShortNumberCost::TOLL_FREE;
        foreach ($regionCodes as $regionCode) {
            $costForRegion = $this->getExpectedCostForRegion($number, $regionCode);
            switch ($costForRegion) {
                case ShortNumberCost::PREMIUM_RATE:
                    return ShortNumberCost::PREMIUM_RATE;
                case ShortNumberCost::UNKNOWN_COST:
                    $cost = ShortNumberCost::UNKNOWN_COST;
                    break;
                case ShortNumberCost::STANDARD_RATE:
                    if ($cost != ShortNumberCost::UNKNOWN_COST) {
                        $cost = ShortNumberCost::STANDARD_RATE;
                    }
                    break;
                case ShortNumberCost::TOLL_FREE:
                    break;
            }
        }
        return $cost;
    }
    public function isEmergencyNumber($number, $regionCode)
    {
        return $this->matchesEmergencyNumberHelper($number, $regionCode, false );
    }
    private function getNationalSignificantNumber(PhoneNumber $number)
    {
        $nationalNumber = '';
        if ($number->isItalianLeadingZero()) {
            $zeros = str_repeat('0', $number->getNumberOfLeadingZeros());
            $nationalNumber .= $zeros;
        }
        $nationalNumber .= $number->getNationalNumber();
        return $nationalNumber;
    }
    private function matchesPossibleNumberAndNationalNumber($number, PhoneNumberDesc $numberDesc)
    {
        return ($this->matcherAPI->matchesPossibleNumber($number, $numberDesc)
            && $this->matcherAPI->matchesNationalNumber($number, $numberDesc, false));
    }
}
