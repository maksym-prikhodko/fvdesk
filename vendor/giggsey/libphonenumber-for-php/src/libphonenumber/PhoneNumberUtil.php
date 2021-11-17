<?php
namespace libphonenumber;
class PhoneNumberUtil
{
    const REGEX_FLAGS = 'ui'; 
    const MIN_LENGTH_FOR_NSN = 2;
    const MAX_LENGTH_FOR_NSN = 17;
    const MAX_INPUT_STRING_LENGTH = 250;
    const MAX_LENGTH_COUNTRY_CODE = 3;
    const REGION_CODE_FOR_NON_GEO_ENTITY = "001";
    const META_DATA_FILE_PREFIX = 'PhoneNumberMetadata';
    const TEST_META_DATA_FILE_PREFIX = 'PhoneNumberMetadataForTesting';
    const UNKNOWN_REGION = "ZZ";
    const NANPA_COUNTRY_CODE = 1;
    const COLOMBIA_MOBILE_TO_FIXED_LINE_PREFIX = "3";
    const PLUS_SIGN = '+';
    const PLUS_CHARS = '+＋';
    const STAR_SIGN = '*';
    const RFC3966_EXTN_PREFIX = ";ext=";
    const RFC3966_PREFIX = "tel:";
    const RFC3966_PHONE_CONTEXT = ";phone-context=";
    const RFC3966_ISDN_SUBADDRESS = ";isub=";
    const VALID_ALPHA_PHONE_PATTERN = "(?:.*?[A-Za-z]){3}.*";
    const VALID_ALPHA = "A-Za-z";
    const DEFAULT_EXTN_PREFIX = " ext. ";
    const VALID_PUNCTUATION = "-x\xE2\x80\x90-\xE2\x80\x95\xE2\x88\x92\xE3\x83\xBC\xEF\xBC\x8D-\xEF\xBC\x8F \xC2\xA0\xC2\xAD\xE2\x80\x8B\xE2\x81\xA0\xE3\x80\x80()\xEF\xBC\x88\xEF\xBC\x89\xEF\xBC\xBB\xEF\xBC\xBD.\\[\\]/~\xE2\x81\x93\xE2\x88\xBC";
    const DIGITS = "\\p{Nd}";
    const UNIQUE_INTERNATIONAL_PREFIX = "[\\d]+(?:[~\xE2\x81\x93\xE2\x88\xBC\xEF\xBD\x9E][\\d]+)?";
    const NON_DIGITS_PATTERN = "(\\D+)";
    const FIRST_GROUP_PATTERN = "(\\$\\d)";
    const NP_PATTERN = '\\$NP';
    const FG_PATTERN = '\\$FG';
    const CC_PATTERN = '\\$CC';
    const FIRST_GROUP_ONLY_PREFIX_PATTERN = '\\(?\\$1\\)?';
    public static $PLUS_CHARS_PATTERN;
    private static $SEPARATOR_PATTERN;
    private static $CAPTURING_DIGIT_PATTERN;
    private static $VALID_START_CHAR_PATTERN = null;
    private static $SECOND_NUMBER_START_PATTERN = "[\\\\/] *x";
    private static $UNWANTED_END_CHAR_PATTERN = "[[\\P{N}&&\\P{L}]&&[^#]]+$";
    private static $DIALLABLE_CHAR_MAPPINGS = array();
    private static $CAPTURING_EXTN_DIGITS;
    private static $instance = null;
    private static $ALPHA_MAPPINGS = array(
        'A' => '2',
        'B' => '2',
        'C' => '2',
        'D' => '3',
        'E' => '3',
        'F' => '3',
        'G' => '4',
        'H' => '4',
        'I' => '4',
        'J' => '5',
        'K' => '5',
        'L' => '5',
        'M' => '6',
        'N' => '6',
        'O' => '6',
        'P' => '7',
        'Q' => '7',
        'R' => '7',
        'S' => '7',
        'T' => '8',
        'U' => '8',
        'V' => '8',
        'W' => '9',
        'X' => '9',
        'Y' => '9',
        'Z' => '9',
    );
    private static $MOBILE_TOKEN_MAPPINGS;
    private static $ALPHA_PHONE_MAPPINGS;
    private static $ALL_PLUS_NUMBER_GROUPING_SYMBOLS;
    private static $asciiDigitMappings;
    private static $EXTN_PATTERNS_FOR_PARSING;
    private static $EXTN_PATTERNS_FOR_MATCHING;
    private static $EXTN_PATTERN = null;
    private static $VALID_PHONE_NUMBER_PATTERN;
    private static $MIN_LENGTH_PHONE_NUMBER_PATTERN;
    private static $VALID_PHONE_NUMBER;
    private static $numericCharacters = array();
    private $metadataLoader;
    private $regionToMetadataMap = array();
    private $countryCodeToNonGeographicalMetadataMap = array();
    private $countryCodesForNonGeographicalRegion = array();
    private $supportedRegions = array();
    private $currentFilePrefix = self::META_DATA_FILE_PREFIX;
    private $countryCallingCodeToRegionCodeMap = array();
    private $nanpaRegions = array();
    private function __construct($filePrefix, MetadataLoaderInterface $metadataLoader, $countryCallingCodeToRegionCodeMap)
    {
        $this->metadataLoader = $metadataLoader;
        $this->countryCallingCodeToRegionCodeMap = $countryCallingCodeToRegionCodeMap;
        $this->init($filePrefix);
        self::initCapturingExtnDigits();
        self::initExtnPatterns();
        self::initAsciiDigitMappings();
        self::initExtnPattern();
        self::$PLUS_CHARS_PATTERN = "[" . self::PLUS_CHARS . "]+";
        self::$SEPARATOR_PATTERN = "[" . self::VALID_PUNCTUATION . "]+";
        self::$CAPTURING_DIGIT_PATTERN = "(" . self::DIGITS . ")";
        self::$VALID_START_CHAR_PATTERN = "[" . self::PLUS_CHARS . self::DIGITS . "]";
        self::$ALPHA_PHONE_MAPPINGS = self::$ALPHA_MAPPINGS + self::$asciiDigitMappings;
        self::$DIALLABLE_CHAR_MAPPINGS = self::$asciiDigitMappings;
        self::$DIALLABLE_CHAR_MAPPINGS[self::PLUS_SIGN] = self::PLUS_SIGN;
        self::$DIALLABLE_CHAR_MAPPINGS['*'] = '*';
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS = array();
        foreach (self::$ALPHA_MAPPINGS as $c => $value) {
            self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS[strtolower($c)] = $c;
            self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS[$c] = $c;
        }
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS += self::$asciiDigitMappings;
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["-"] = '-';
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["\xEF\xBC\x8D"] = '-';
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["\xE2\x80\x90"] = '-';
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["\xE2\x80\x91"] = '-';
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["\xE2\x80\x92"] = '-';
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["\xE2\x80\x93"] = '-';
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["\xE2\x80\x94"] = '-';
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["\xE2\x80\x95"] = '-';
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["\xE2\x88\x92"] = '-';
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["/"] = "/";
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["\xEF\xBC\x8F"] = "/";
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS[" "] = " ";
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["\xE3\x80\x80"] = " ";
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["\xE2\x81\xA0"] = " ";
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["."] = ".";
        self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS["\xEF\xBC\x8E"] = ".";
        self::$MIN_LENGTH_PHONE_NUMBER_PATTERN = "[" . self::DIGITS . "]{" . self::MIN_LENGTH_FOR_NSN . "}";
        self::$VALID_PHONE_NUMBER = "[" . self::PLUS_CHARS . "]*(?:[" . self::VALID_PUNCTUATION . self::STAR_SIGN . "]*[" . self::DIGITS . "]){3,}[" . self::VALID_PUNCTUATION . self::STAR_SIGN . self::VALID_ALPHA . self::DIGITS . "]*";
        self::$VALID_PHONE_NUMBER_PATTERN = "%^" . self::$MIN_LENGTH_PHONE_NUMBER_PATTERN . "$|^" . self::$VALID_PHONE_NUMBER . "(?:" . self::$EXTN_PATTERNS_FOR_PARSING . ")?%" . self::REGEX_FLAGS;
        self::$UNWANTED_END_CHAR_PATTERN = "[^" . self::DIGITS . self::VALID_ALPHA . "#]+$";
        self::$MOBILE_TOKEN_MAPPINGS = array();
        self::$MOBILE_TOKEN_MAPPINGS['52'] = "1";
        self::$MOBILE_TOKEN_MAPPINGS['54'] = "9";
        self::loadNumericCharacters();
    }
    public static function getInstance($baseFileLocation = self::META_DATA_FILE_PREFIX, array $countryCallingCodeToRegionCodeMap = null, MetadataLoaderInterface $metadataLoader = null)
    {
        if (self::$instance === null) {
            if ($countryCallingCodeToRegionCodeMap === null) {
                $countryCallingCodeToRegionCodeMap = CountryCodeToRegionCodeMap::$countryCodeToRegionCodeMap;
            }
            if ($metadataLoader === null) {
                $metadataLoader = new DefaultMetadataLoader();
            }
            self::$instance = new PhoneNumberUtil($baseFileLocation, $metadataLoader, $countryCallingCodeToRegionCodeMap);
        }
        return self::$instance;
    }
    private static function loadNumericCharacters()
    {
        self::$numericCharacters[pack("H*", 'efbc90')] = 0;
        self::$numericCharacters[pack("H*", 'efbc91')] = 1;
        self::$numericCharacters[pack("H*", 'efbc92')] = 2;
        self::$numericCharacters[pack("H*", 'efbc93')] = 3;
        self::$numericCharacters[pack("H*", 'efbc94')] = 4;
        self::$numericCharacters[pack("H*", 'efbc95')] = 5;
        self::$numericCharacters[pack("H*", 'efbc96')] = 6;
        self::$numericCharacters[pack("H*", 'efbc97')] = 7;
        self::$numericCharacters[pack("H*", 'efbc98')] = 8;
        self::$numericCharacters[pack("H*", 'efbc99')] = 9;
        self::$numericCharacters[pack("H*", 'd9a0')] = 0;
        self::$numericCharacters[pack("H*", 'd9a1')] = 1;
        self::$numericCharacters[pack("H*", 'd9a2')] = 2;
        self::$numericCharacters[pack("H*", 'd9a3')] = 3;
        self::$numericCharacters[pack("H*", 'd9a4')] = 4;
        self::$numericCharacters[pack("H*", 'd9a5')] = 5;
        self::$numericCharacters[pack("H*", 'd9a6')] = 6;
        self::$numericCharacters[pack("H*", 'd9a7')] = 7;
        self::$numericCharacters[pack("H*", 'd9a8')] = 8;
        self::$numericCharacters[pack("H*", 'd9a9')] = 9;
        self::$numericCharacters[pack("H*", 'dbb0')] = 0;
        self::$numericCharacters[pack("H*", 'dbb1')] = 1;
        self::$numericCharacters[pack("H*", 'dbb2')] = 2;
        self::$numericCharacters[pack("H*", 'dbb3')] = 3;
        self::$numericCharacters[pack("H*", 'dbb4')] = 4;
        self::$numericCharacters[pack("H*", 'dbb5')] = 5;
        self::$numericCharacters[pack("H*", 'dbb6')] = 6;
        self::$numericCharacters[pack("H*", 'dbb7')] = 7;
        self::$numericCharacters[pack("H*", 'dbb8')] = 8;
        self::$numericCharacters[pack("H*", 'dbb9')] = 9;
        self::$numericCharacters[pack("H*", 'e1a090')] = 0;
        self::$numericCharacters[pack("H*", 'e1a091')] = 1;
        self::$numericCharacters[pack("H*", 'e1a092')] = 2;
        self::$numericCharacters[pack("H*", 'e1a093')] = 3;
        self::$numericCharacters[pack("H*", 'e1a094')] = 4;
        self::$numericCharacters[pack("H*", 'e1a095')] = 5;
        self::$numericCharacters[pack("H*", 'e1a096')] = 6;
        self::$numericCharacters[pack("H*", 'e1a097')] = 7;
        self::$numericCharacters[pack("H*", 'e1a098')] = 8;
        self::$numericCharacters[pack("H*", 'e1a099')] = 9;
    }
    private function init($filePrefix)
    {
        $this->currentFilePrefix = dirname(__FILE__) . '/data/' . $filePrefix;
        foreach ($this->countryCallingCodeToRegionCodeMap as $countryCode => $regionCodes) {
            if (count($regionCodes) == 1 && self::REGION_CODE_FOR_NON_GEO_ENTITY === $regionCodes[0]) {
                $this->countryCodesForNonGeographicalRegion[] = $countryCode;
            } else {
                $this->supportedRegions = array_merge($this->supportedRegions, $regionCodes);
            }
        }
        $idx_region_code_non_geo_entity = array_search(self::REGION_CODE_FOR_NON_GEO_ENTITY, $this->supportedRegions);
        if ($idx_region_code_non_geo_entity !== false) {
            unset($this->supportedRegions[$idx_region_code_non_geo_entity]);
        }
        $this->nanpaRegions = $this->countryCallingCodeToRegionCodeMap[self::NANPA_COUNTRY_CODE];
    }
    private static function initCapturingExtnDigits()
    {
        self::$CAPTURING_EXTN_DIGITS = "(" . self::DIGITS . "{1,7})";
    }
    private static function initExtnPatterns()
    {
        $singleExtnSymbolsForMatching = "x\xEF\xBD\x98#\xEF\xBC\x83~\xEF\xBD\x9E";
        $singleExtnSymbolsForParsing = "," . $singleExtnSymbolsForMatching;
        self::$EXTN_PATTERNS_FOR_PARSING = self::createExtnPattern($singleExtnSymbolsForParsing);
        self::$EXTN_PATTERNS_FOR_MATCHING = self::createExtnPattern($singleExtnSymbolsForMatching);
    }
    private static function createExtnPattern($singleExtnSymbols)
    {
        return (self::RFC3966_EXTN_PREFIX . self::$CAPTURING_EXTN_DIGITS . "|" . "[ \xC2\xA0\\t,]*" .
            "(?:e?xt(?:ensi(?:o\xCC\x81?|\xC3\xB3))?n?|(?:\xEF\xBD\x85)?\xEF\xBD\x98\xEF\xBD\x94(?:\xEF\xBD\x8E)?|" .
            "[" . $singleExtnSymbols . "]|int|\xEF\xBD\x89\xEF\xBD\x8E\xEF\xBD\x94|anexo)" .
            "[:\\.\xEF\xBC\x8E]?[ \xC2\xA0\\t,-]*" . self::$CAPTURING_EXTN_DIGITS . "#?|" .
            "[- ]+(" . self::DIGITS . "{1,5})#");
    }
    private static function initAsciiDigitMappings()
    {
        self::$asciiDigitMappings = array(
            '0' => '0',
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
            '7' => '7',
            '8' => '8',
            '9' => '9',
        );
    }
    private static function initExtnPattern()
    {
        self::$EXTN_PATTERN = "/(?:" . self::$EXTN_PATTERNS_FOR_PARSING . ")$/" . self::REGEX_FLAGS;
    }
    public static function resetInstance()
    {
        self::$instance = null;
    }
    public static function convertAlphaCharactersInNumber($number)
    {
        return self::normalizeHelper($number, self::$ALPHA_PHONE_MAPPINGS, false);
    }
    private static function normalizeHelper($number, array $normalizationReplacements, $removeNonMatches)
    {
        $normalizedNumber = "";
        $strLength = mb_strlen($number, 'UTF-8');
        for ($i = 0; $i < $strLength; $i++) {
            $character = mb_substr($number, $i, 1, 'UTF-8');
            if (isset($normalizationReplacements[mb_strtoupper($character, 'UTF-8')])) {
                $normalizedNumber .= $normalizationReplacements[mb_strtoupper($character, 'UTF-8')];
            } else {
                if (!$removeNonMatches) {
                    $normalizedNumber .= $character;
                }
            }
        }
        return $normalizedNumber;
    }
    public static function formattingRuleHasFirstGroupOnly($nationalPrefixFormattingRule)
    {
        $m = preg_match(self::FIRST_GROUP_ONLY_PREFIX_PATTERN, $nationalPrefixFormattingRule);
        return $m > 0;
    }
    public function getSupportedRegions()
    {
        return $this->supportedRegions;
    }
    public function getSupportedGlobalNetworkCallingCodes()
    {
        return $this->countryCodesForNonGeographicalRegion;
    }
    public function getLengthOfGeographicalAreaCode(PhoneNumber $number)
    {
        $metadata = $this->getMetadataForRegion($this->getRegionCodeForNumber($number));
        if ($metadata === null) {
            return 0;
        }
        if (!$metadata->hasNationalPrefix() && !$number->isItalianLeadingZero()) {
            return 0;
        }
        if (!$this->isNumberGeographical($number)) {
            return 0;
        }
        return $this->getLengthOfNationalDestinationCode($number);
    }
    public function getMetadataForRegion($regionCode)
    {
        if (!$this->isValidRegionCode($regionCode)) {
            return null;
        }
        if (!isset($this->regionToMetadataMap[$regionCode])) {
            $this->loadMetadataFromFile($this->currentFilePrefix, $regionCode, 0, $this->metadataLoader);
        }
        return isset($this->regionToMetadataMap[$regionCode]) ? $this->regionToMetadataMap[$regionCode] : null;
    }
    private function isValidRegionCode($regionCode)
    {
        return $regionCode !== null && in_array($regionCode, $this->supportedRegions);
    }
    public function loadMetadataFromFile($filePrefix, $regionCode, $countryCallingCode, MetadataLoaderInterface $metadataLoader)
    {
        $isNonGeoRegion = self::REGION_CODE_FOR_NON_GEO_ENTITY === $regionCode;
        $fileName = $filePrefix . '_' . ($isNonGeoRegion ? $countryCallingCode : $regionCode) . '.php';
        if (!is_readable($fileName)) {
            throw new \RuntimeException('missing metadata: ' . $fileName);
        } else {
            $data = $metadataLoader->loadMetadata($fileName);
            $metadata = new PhoneMetadata();
            $metadata->fromArray($data);
            if ($isNonGeoRegion) {
                $this->countryCodeToNonGeographicalMetadataMap[$countryCallingCode] = $metadata;
            } else {
                $this->regionToMetadataMap[$regionCode] = $metadata;
            }
        }
    }
    public function getRegionCodeForNumber(PhoneNumber $number)
    {
        $countryCode = $number->getCountryCode();
        if (!isset($this->countryCallingCodeToRegionCodeMap[$countryCode])) {
            return null;
        }
        $regions = $this->countryCallingCodeToRegionCodeMap[$countryCode];
        if (count($regions) == 1) {
            return $regions[0];
        } else {
            return $this->getRegionCodeForNumberFromRegionList($number, $regions);
        }
    }
    private function getRegionCodeForNumberFromRegionList(PhoneNumber $number, array $regionCodes)
    {
        $nationalNumber = $this->getNationalSignificantNumber($number);
        foreach ($regionCodes as $regionCode) {
            $metadata = $this->getMetadataForRegion($regionCode);
            if ($metadata->hasLeadingDigits()) {
                $nbMatches = preg_match(
                    '/' . $metadata->getLeadingDigits() . '/',
                    $nationalNumber,
                    $matches,
                    PREG_OFFSET_CAPTURE
                );
                if ($nbMatches > 0 && $matches[0][1] === 0) {
                    return $regionCode;
                }
            } else if ($this->getNumberTypeHelper($nationalNumber, $metadata) != PhoneNumberType::UNKNOWN) {
                return $regionCode;
            }
        }
        return null;
    }
    public function getNationalSignificantNumber(PhoneNumber $number)
    {
        $nationalNumber = '';
        if ($number->isItalianLeadingZero()) {
            $zeros = str_repeat('0', $number->getNumberOfLeadingZeros());
            $nationalNumber .= $zeros;
        }
        $nationalNumber .= $number->getNationalNumber();
        return $nationalNumber;
    }
    private function getNumberTypeHelper($nationalNumber, PhoneMetadata $metadata)
    {
        if (!$this->isNumberMatchingDesc($nationalNumber, $metadata->getGeneralDesc())) {
            return PhoneNumberType::UNKNOWN;
        }
        if ($this->isNumberMatchingDesc($nationalNumber, $metadata->getPremiumRate())) {
            return PhoneNumberType::PREMIUM_RATE;
        }
        if ($this->isNumberMatchingDesc($nationalNumber, $metadata->getTollFree())) {
            return PhoneNumberType::TOLL_FREE;
        }
        if ($this->isNumberMatchingDesc($nationalNumber, $metadata->getSharedCost())) {
            return PhoneNumberType::SHARED_COST;
        }
        if ($this->isNumberMatchingDesc($nationalNumber, $metadata->getVoip())) {
            return PhoneNumberType::VOIP;
        }
        if ($this->isNumberMatchingDesc($nationalNumber, $metadata->getPersonalNumber())) {
            return PhoneNumberType::PERSONAL_NUMBER;
        }
        if ($this->isNumberMatchingDesc($nationalNumber, $metadata->getPager())) {
            return PhoneNumberType::PAGER;
        }
        if ($this->isNumberMatchingDesc($nationalNumber, $metadata->getUan())) {
            return PhoneNumberType::UAN;
        }
        if ($this->isNumberMatchingDesc($nationalNumber, $metadata->getVoicemail())) {
            return PhoneNumberType::VOICEMAIL;
        }
        $isFixedLine = $this->isNumberMatchingDesc($nationalNumber, $metadata->getFixedLine());
        if ($isFixedLine) {
            if ($metadata->isSameMobileAndFixedLinePattern()) {
                return PhoneNumberType::FIXED_LINE_OR_MOBILE;
            } else if ($this->isNumberMatchingDesc($nationalNumber, $metadata->getMobile())) {
                return PhoneNumberType::FIXED_LINE_OR_MOBILE;
            }
            return PhoneNumberType::FIXED_LINE;
        }
        if (!$metadata->isSameMobileAndFixedLinePattern() &&
            $this->isNumberMatchingDesc($nationalNumber, $metadata->getMobile())
        ) {
            return PhoneNumberType::MOBILE;
        }
        return PhoneNumberType::UNKNOWN;
    }
    public function isNumberMatchingDesc($nationalNumber, PhoneNumberDesc $numberDesc)
    {
        $nationalNumberPatternMatcher = new Matcher($numberDesc->getNationalNumberPattern(), $nationalNumber);
        return $this->isNumberPossibleForDesc($nationalNumber, $numberDesc) && $nationalNumberPatternMatcher->matches();
    }
    private function isShorterThanPossibleNormalNumber(PhoneMetadata $regionMetadata, $number)
    {
        $possibleNumberPattern = $regionMetadata->getGeneralDesc()->getPossibleNumberPattern();
        return ($this->testNumberLengthAgainstPattern($possibleNumberPattern, $number) === ValidationResult::TOO_SHORT);
    }
    public function isNumberPossibleForDesc($nationalNumber, PhoneNumberDesc $numberDesc)
    {
        $possibleNumberPatternMatcher = new Matcher($numberDesc->getPossibleNumberPattern(), $nationalNumber);
        return $possibleNumberPatternMatcher->matches();
    }
    public function isNumberGeographical(PhoneNumber $phoneNumber)
    {
        $numberType = $this->getNumberType($phoneNumber);
        return $numberType == PhoneNumberType::FIXED_LINE || $numberType == PhoneNumberType::FIXED_LINE_OR_MOBILE;
    }
    public function getNumberType(PhoneNumber $number)
    {
        $regionCode = $this->getRegionCodeForNumber($number);
        $metadata = $this->getMetadataForRegionOrCallingCode($number->getCountryCode(), $regionCode);
        if ($metadata === null) {
            return PhoneNumberType::UNKNOWN;
        }
        $nationalSignificantNumber = $this->getNationalSignificantNumber($number);
        return $this->getNumberTypeHelper($nationalSignificantNumber, $metadata);
    }
    private function getMetadataForRegionOrCallingCode($countryCallingCode, $regionCode)
    {
        return self::REGION_CODE_FOR_NON_GEO_ENTITY === $regionCode ?
            $this->getMetadataForNonGeographicalRegion($countryCallingCode) : $this->getMetadataForRegion($regionCode);
    }
    public function getMetadataForNonGeographicalRegion($countryCallingCode)
    {
        if (!isset($this->countryCallingCodeToRegionCodeMap[$countryCallingCode])) {
            return null;
        }
        if (!isset($this->countryCodeToNonGeographicalMetadataMap[$countryCallingCode])) {
            $this->loadMetadataFromFile(
                $this->currentFilePrefix,
                self::REGION_CODE_FOR_NON_GEO_ENTITY,
                $countryCallingCode,
                $this->metadataLoader
            );
        }
        return $this->countryCodeToNonGeographicalMetadataMap[$countryCallingCode];
    }
    public function getLengthOfNationalDestinationCode(PhoneNumber $number)
    {
        if ($number->hasExtension()) {
            $copiedProto = new PhoneNumber();
            $copiedProto->mergeFrom($number);
            $copiedProto->clearExtension();
        } else {
            $copiedProto = clone $number;
        }
        $nationalSignificantNumber = $this->format($copiedProto, PhoneNumberFormat::INTERNATIONAL);
        $numberGroups = preg_split('/' . self::NON_DIGITS_PATTERN . '/', $nationalSignificantNumber);
        if (count($numberGroups) <= 3) {
            return 0;
        }
        if ($this->getNumberType($number) == PhoneNumberType::MOBILE) {
            $mobileToken = self::getCountryMobileToken($number->getCountryCode());
            if ($mobileToken !== "") {
                return mb_strlen($numberGroups[2]) + mb_strlen($numberGroups[3]);
            }
        }
        return mb_strlen($numberGroups[2]);
    }
    public function format(PhoneNumber $number, $numberFormat)
    {
        if ($number->getNationalNumber() == 0 && $number->hasRawInput()) {
            $rawInput = $number->getRawInput();
            if (mb_strlen($rawInput) > 0) {
                return $rawInput;
            }
        }
        $metadata = null;
        $formattedNumber = "";
        $countryCallingCode = $number->getCountryCode();
        $nationalSignificantNumber = $this->getNationalSignificantNumber($number);
        if ($numberFormat == PhoneNumberFormat::E164) {
            $formattedNumber .= $nationalSignificantNumber;
            $this->prefixNumberWithCountryCallingCode($countryCallingCode, PhoneNumberFormat::E164, $formattedNumber);
        } elseif (!$this->hasValidCountryCallingCode($countryCallingCode)) {
            $formattedNumber .= $nationalSignificantNumber;
        } else {
            $regionCode = $this->getRegionCodeForCountryCode($countryCallingCode);
            $metadata = $this->getMetadataForRegionOrCallingCode($countryCallingCode, $regionCode);
            $formattedNumber .= $this->formatNsn($nationalSignificantNumber, $metadata, $numberFormat);
            $this->prefixNumberWithCountryCallingCode($countryCallingCode, $numberFormat, $formattedNumber);
        }
        $this->maybeAppendFormattedExtension($number, $metadata, $numberFormat, $formattedNumber);
        return $formattedNumber;
    }
    private function prefixNumberWithCountryCallingCode($countryCallingCode, $numberFormat, &$formattedNumber)
    {
        switch ($numberFormat) {
            case PhoneNumberFormat::E164:
                $formattedNumber = self::PLUS_SIGN . $countryCallingCode . $formattedNumber;
                return;
            case PhoneNumberFormat::INTERNATIONAL:
                $formattedNumber = self::PLUS_SIGN . $countryCallingCode . " " . $formattedNumber;
                return;
            case PhoneNumberFormat::RFC3966:
                $formattedNumber = self::RFC3966_PREFIX . self::PLUS_SIGN . $countryCallingCode . "-" . $formattedNumber;
                return;
            case PhoneNumberFormat::NATIONAL:
            default:
                return;
        }
    }
    private function hasValidCountryCallingCode($countryCallingCode)
    {
        return isset($this->countryCallingCodeToRegionCodeMap[$countryCallingCode]);
    }
    public function getRegionCodeForCountryCode($countryCallingCode)
    {
        $regionCodes = isset($this->countryCallingCodeToRegionCodeMap[$countryCallingCode]) ? $this->countryCallingCodeToRegionCodeMap[$countryCallingCode] : null;
        return $regionCodes === null ? self::UNKNOWN_REGION : $regionCodes[0];
    }
    private function formatNsn($number, PhoneMetadata $metadata, $numberFormat, $carrierCode = null)
    {
        $intlNumberFormats = $metadata->intlNumberFormats();
        $availableFormats = (count($intlNumberFormats) == 0 || $numberFormat == PhoneNumberFormat::NATIONAL)
            ? $metadata->numberFormats()
            : $metadata->intlNumberFormats();
        $formattingPattern = $this->chooseFormattingPatternForNumber($availableFormats, $number);
        return ($formattingPattern === null)
            ? $number
            : $this->formatNsnUsingPattern($number, $formattingPattern, $numberFormat, $carrierCode);
    }
    public function chooseFormattingPatternForNumber(array $availableFormats, $nationalNumber)
    {
        foreach ($availableFormats as $numFormat) {
            $leadingDigitsPatternMatcher = null;
            $size = $numFormat->leadingDigitsPatternSize();
            if ($size > 0) {
                $leadingDigitsPatternMatcher = new Matcher(
                    $numFormat->getLeadingDigitsPattern($size - 1),
                    $nationalNumber
                );
            }
            if ($size == 0 || $leadingDigitsPatternMatcher->lookingAt()) {
                $m = new Matcher($numFormat->getPattern(), $nationalNumber);
                if ($m->matches() > 0) {
                    return $numFormat;
                }
            }
        }
        return null;
    }
    private function formatNsnUsingPattern(
        $nationalNumber,
        NumberFormat $formattingPattern,
        $numberFormat,
        $carrierCode = null
    ) {
        $numberFormatRule = $formattingPattern->getFormat();
        $m = new Matcher($formattingPattern->getPattern(), $nationalNumber);
        if ($numberFormat === PhoneNumberFormat::NATIONAL &&
            $carrierCode !== null && mb_strlen($carrierCode) > 0 &&
            mb_strlen($formattingPattern->getDomesticCarrierCodeFormattingRule()) > 0
        ) {
            $carrierCodeFormattingRule = $formattingPattern->getDomesticCarrierCodeFormattingRule();
            $ccPatternMatcher = new Matcher(self::CC_PATTERN, $carrierCodeFormattingRule);
            $carrierCodeFormattingRule = $ccPatternMatcher->replaceFirst($carrierCode);
            $firstGroupMatcher = new Matcher(self::FIRST_GROUP_PATTERN, $numberFormatRule);
            $numberFormatRule = $firstGroupMatcher->replaceFirst($carrierCodeFormattingRule);
            $formattedNationalNumber = $m->replaceAll($numberFormatRule);
        } else {
            $nationalPrefixFormattingRule = $formattingPattern->getNationalPrefixFormattingRule();
            if ($numberFormat == PhoneNumberFormat::NATIONAL &&
                $nationalPrefixFormattingRule !== null &&
                mb_strlen($nationalPrefixFormattingRule) > 0
            ) {
                $firstGroupMatcher = new Matcher(self::FIRST_GROUP_PATTERN, $numberFormatRule);
                $formattedNationalNumber = $m->replaceAll(
                    $firstGroupMatcher->replaceFirst($nationalPrefixFormattingRule)
                );
            } else {
                $formattedNationalNumber = $m->replaceAll($numberFormatRule);
            }
        }
        if ($numberFormat == PhoneNumberFormat::RFC3966) {
            $matcher = new Matcher(self::$SEPARATOR_PATTERN, $formattedNationalNumber);
            if ($matcher->lookingAt()) {
                $formattedNationalNumber = $matcher->replaceFirst("");
            }
            $formattedNationalNumber = $matcher->reset($formattedNationalNumber)->replaceAll("-");
        }
        return $formattedNationalNumber;
    }
    private function maybeAppendFormattedExtension(PhoneNumber $number, $metadata, $numberFormat, &$formattedNumber)
    {
        if ($number->hasExtension() && mb_strlen($number->getExtension()) > 0) {
            if ($numberFormat === PhoneNumberFormat::RFC3966) {
                $formattedNumber .= self::RFC3966_EXTN_PREFIX . $number->getExtension();
            } else {
                if (!empty($metadata) && $metadata->hasPreferredExtnPrefix()) {
                    $formattedNumber .= $metadata->getPreferredExtnPrefix() . $number->getExtension();
                } else {
                    $formattedNumber .= self::DEFAULT_EXTN_PREFIX . $number->getExtension();
                }
            }
        }
    }
    public static function getCountryMobileToken($countryCallingCode)
    {
        if (array_key_exists($countryCallingCode, self::$MOBILE_TOKEN_MAPPINGS)) {
            return self::$MOBILE_TOKEN_MAPPINGS[$countryCallingCode];
        }
        return "";
    }
    public function isAlphaNumber($number)
    {
        if (!$this->isViablePhoneNumber($number)) {
            return false;
        }
        $this->maybeStripExtension($number);
        return (bool)preg_match('/' . self::VALID_ALPHA_PHONE_PATTERN . '/' . self::REGEX_FLAGS, $number);
    }
    public static function isViablePhoneNumber($number)
    {
        if (mb_strlen($number) < self::MIN_LENGTH_FOR_NSN) {
            return false;
        }
        $validPhoneNumberPattern = self::getValidPhoneNumberPattern();
        $m = preg_match($validPhoneNumberPattern, $number);
        return $m > 0;
    }
    private static function getValidPhoneNumberPattern()
    {
        return self::$VALID_PHONE_NUMBER_PATTERN;
    }
    private function maybeStripExtension(&$number)
    {
        $matches = array();
        $find = preg_match(self::$EXTN_PATTERN, $number, $matches, PREG_OFFSET_CAPTURE);
        if ($find > 0 && $this->isViablePhoneNumber(substr($number, 0, $matches[0][1]))) {
            for ($i = 1, $length = count($matches); $i <= $length; $i++) {
                if ($matches[$i][0] != "") {
                    $extension = $matches[$i][0];
                    $number = substr($number, 0, $matches[0][1]);
                    return $extension;
                }
            }
        }
        return "";
    }
    public function parseAndKeepRawInput($numberToParse, $defaultRegion, PhoneNumber $phoneNumber = null)
    {
        if ($phoneNumber === null) {
            $phoneNumber = new PhoneNumber();
        }
        $this->parseHelper($numberToParse, $defaultRegion, true, true, $phoneNumber);
        return $phoneNumber;
    }
    public static function setItalianLeadingZerosForPhoneNumber($nationalNumber, PhoneNumber $phoneNumber)
    {
        if (strlen($nationalNumber) > 1 && substr($nationalNumber, 0, 1) == '0') {
            $phoneNumber->setItalianLeadingZero(true);
            $numberOfLeadingZeros = 1;
            while ($numberOfLeadingZeros < (strlen($nationalNumber) - 1) &&
                substr($nationalNumber, $numberOfLeadingZeros, 1) == '0') {
                $numberOfLeadingZeros++;
            }
            if ($numberOfLeadingZeros != 1) {
                $phoneNumber->setNumberOfLeadingZeros($numberOfLeadingZeros);
            }
        }
    }
    private function parseHelper($numberToParse, $defaultRegion, $keepRawInput, $checkRegion, PhoneNumber $phoneNumber)
    {
        if ($numberToParse === null) {
            throw new NumberParseException(NumberParseException::NOT_A_NUMBER, "The phone number supplied was null.");
        }
        $numberToParse = trim($numberToParse);
        if (mb_strlen($numberToParse) > self::MAX_INPUT_STRING_LENGTH) {
            throw new NumberParseException(
                NumberParseException::TOO_LONG,
                "The string supplied was too long to parse."
            );
        }
        $nationalNumber = '';
        $this->buildNationalNumberForParsing($numberToParse, $nationalNumber);
        if (!$this->isViablePhoneNumber($nationalNumber)) {
            throw new NumberParseException(
                NumberParseException::NOT_A_NUMBER,
                "The string supplied did not seem to be a phone number."
            );
        }
        if ($checkRegion && !$this->checkRegionForParsing($nationalNumber, $defaultRegion)) {
            throw new NumberParseException(
                NumberParseException::INVALID_COUNTRY_CODE,
                "Missing or invalid default region."
            );
        }
        if ($keepRawInput) {
            $phoneNumber->setRawInput($numberToParse);
        }
        $extension = $this->maybeStripExtension($nationalNumber);
        if (mb_strlen($extension) > 0) {
            $phoneNumber->setExtension($extension);
        }
        $regionMetadata = $this->getMetadataForRegion($defaultRegion);
        $normalizedNationalNumber = "";
        try {
            $countryCode = $this->maybeExtractCountryCode(
                $nationalNumber,
                $regionMetadata,
                $normalizedNationalNumber,
                $keepRawInput,
                $phoneNumber
            );
        } catch (NumberParseException $e) {
            $matcher = new Matcher(self::$PLUS_CHARS_PATTERN, $nationalNumber);
            if ($e->getErrorType() == NumberParseException::INVALID_COUNTRY_CODE && $matcher->lookingAt()) {
                $countryCode = $this->maybeExtractCountryCode(
                    substr($nationalNumber, $matcher->end()),
                    $regionMetadata,
                    $normalizedNationalNumber,
                    $keepRawInput,
                    $phoneNumber
                );
                if ($countryCode == 0) {
                    throw new NumberParseException(
                        NumberParseException::INVALID_COUNTRY_CODE,
                        "Could not interpret numbers after plus-sign."
                    );
                }
            } else {
                throw new NumberParseException($e->getErrorType(), $e->getMessage(), $e);
            }
        }
        if ($countryCode !== 0) {
            $phoneNumberRegion = $this->getRegionCodeForCountryCode($countryCode);
            if ($phoneNumberRegion != $defaultRegion) {
                $regionMetadata = $this->getMetadataForRegionOrCallingCode($countryCode, $phoneNumberRegion);
            }
        } else {
            $normalizedNationalNumber .= $this->normalize($nationalNumber);
            if ($defaultRegion !== null) {
                $countryCode = $regionMetadata->getCountryCode();
                $phoneNumber->setCountryCode($countryCode);
            } else if ($keepRawInput) {
                $phoneNumber->clearCountryCodeSource();
            }
        }
        if (mb_strlen($normalizedNationalNumber) < self::MIN_LENGTH_FOR_NSN) {
            throw new NumberParseException(
                NumberParseException::TOO_SHORT_NSN,
                "The string supplied is too short to be a phone number."
            );
        }
        if ($regionMetadata !== null) {
            $carrierCode = "";
            $potentialNationalNumber = $normalizedNationalNumber;
            $this->maybeStripNationalPrefixAndCarrierCode($potentialNationalNumber, $regionMetadata, $carrierCode);
            if (!$this->isShorterThanPossibleNormalNumber($regionMetadata, $potentialNationalNumber)) {
                $normalizedNationalNumber = $potentialNationalNumber;
                if ($keepRawInput) {
                    $phoneNumber->setPreferredDomesticCarrierCode($carrierCode);
                }
            }
        }
        $lengthOfNationalNumber = mb_strlen($normalizedNationalNumber);
        if ($lengthOfNationalNumber < self::MIN_LENGTH_FOR_NSN) {
            throw new NumberParseException(
                NumberParseException::TOO_SHORT_NSN,
                "The string supplied is too short to be a phone number."
            );
        }
        if ($lengthOfNationalNumber > self::MAX_LENGTH_FOR_NSN) {
            throw new NumberParseException(
                NumberParseException::TOO_LONG,
                "The string supplied is too long to be a phone number."
            );
        }
        $this->setItalianLeadingZerosForPhoneNumber($normalizedNationalNumber, $phoneNumber);
        if ((int)$normalizedNationalNumber == 0) {
            $normalizedNationalNumber = "0";
        } else {
            $normalizedNationalNumber = ltrim($normalizedNationalNumber, '0');
        }
        $phoneNumber->setNationalNumber($normalizedNationalNumber);
    }
    private function buildNationalNumberForParsing($numberToParse, &$nationalNumber)
    {
        $indexOfPhoneContext = strpos($numberToParse, self::RFC3966_PHONE_CONTEXT);
        if ($indexOfPhoneContext > 0) {
            $phoneContextStart = $indexOfPhoneContext + mb_strlen(self::RFC3966_PHONE_CONTEXT);
            if (substr($numberToParse, $phoneContextStart, 1) == self::PLUS_SIGN) {
                $phoneContextEnd = strpos($numberToParse, ';', $phoneContextStart);
                if ($phoneContextEnd > 0) {
                    $nationalNumber .= substr($numberToParse, $phoneContextStart, $phoneContextEnd - $phoneContextStart);
                } else {
                    $nationalNumber .= substr($numberToParse, $phoneContextStart);
                }
            }
            $indexOfRfc3966Prefix = strpos($numberToParse, self::RFC3966_PREFIX);
            $indexOfNationalNumber = ($indexOfRfc3966Prefix !== false) ? $indexOfRfc3966Prefix + strlen(self::RFC3966_PREFIX) : 0;
            $nationalNumber .= substr($numberToParse, $indexOfNationalNumber, ($indexOfPhoneContext - $indexOfNationalNumber));
        } else {
            $nationalNumber .= $this->extractPossibleNumber($numberToParse);
        }
        $indexOfIsdn = strpos($nationalNumber, self::RFC3966_ISDN_SUBADDRESS);
        if ($indexOfIsdn > 0) {
            $nationalNumber = substr($nationalNumber, 0, $indexOfIsdn);
        }
    }
    public static function extractPossibleNumber($number)
    {
        $matches = array();
        $match = preg_match('/' . self::$VALID_START_CHAR_PATTERN . '/ui', $number, $matches, PREG_OFFSET_CAPTURE);
        if ($match > 0) {
            $number = substr($number, $matches[0][1]);
            $trailingCharsMatcher = new Matcher(self::$UNWANTED_END_CHAR_PATTERN, $number);
            if ($trailingCharsMatcher->find() && $trailingCharsMatcher->start() > 0) {
                $number = substr($number, 0, $trailingCharsMatcher->start());
            }
            $match = preg_match('%' . self::$SECOND_NUMBER_START_PATTERN . '%', $number, $matches, PREG_OFFSET_CAPTURE);
            if ($match > 0) {
                $number = substr($number, 0, $matches[0][1]);
            }
            return $number;
        } else {
            return "";
        }
    }
    private function checkRegionForParsing($numberToParse, $defaultRegion)
    {
        if (!$this->isValidRegionCode($defaultRegion)) {
            $plusCharsPatternMatcher = new Matcher(self::$PLUS_CHARS_PATTERN, $numberToParse);
            if ($numberToParse === null || mb_strlen($numberToParse) == 0 || !$plusCharsPatternMatcher->lookingAt()) {
                return false;
            }
        }
        return true;
    }
    public function maybeExtractCountryCode(
        $number,
        PhoneMetadata $defaultRegionMetadata = null,
        &$nationalNumber,
        $keepRawInput,
        PhoneNumber $phoneNumber
    ) {
        if (mb_strlen($number) == 0) {
            return 0;
        }
        $fullNumber = $number;
        $possibleCountryIddPrefix = "NonMatch";
        if ($defaultRegionMetadata !== null) {
            $possibleCountryIddPrefix = $defaultRegionMetadata->getInternationalPrefix();
        }
        $countryCodeSource = $this->maybeStripInternationalPrefixAndNormalize($fullNumber, $possibleCountryIddPrefix);
        if ($keepRawInput) {
            $phoneNumber->setCountryCodeSource($countryCodeSource);
        }
        if ($countryCodeSource != CountryCodeSource::FROM_DEFAULT_COUNTRY) {
            if (mb_strlen($fullNumber) <= self::MIN_LENGTH_FOR_NSN) {
                throw new NumberParseException(
                    NumberParseException::TOO_SHORT_AFTER_IDD,
                    "Phone number had an IDD, but after this was not long enough to be a viable phone number."
                );
            }
            $potentialCountryCode = $this->extractCountryCode($fullNumber, $nationalNumber);
            if ($potentialCountryCode != 0) {
                $phoneNumber->setCountryCode($potentialCountryCode);
                return $potentialCountryCode;
            }
            throw new NumberParseException(
                NumberParseException::INVALID_COUNTRY_CODE,
                "Country calling code supplied was not recognised."
            );
        } else if ($defaultRegionMetadata !== null) {
            $defaultCountryCode = $defaultRegionMetadata->getCountryCode();
            $defaultCountryCodeString = (string)$defaultCountryCode;
            $normalizedNumber = (string)$fullNumber;
            if (strpos($normalizedNumber, $defaultCountryCodeString) === 0) {
                $potentialNationalNumber = substr($normalizedNumber, mb_strlen($defaultCountryCodeString));
                $generalDesc = $defaultRegionMetadata->getGeneralDesc();
                $validNumberPattern = $generalDesc->getNationalNumberPattern();
                $carriercode = null;
                $this->maybeStripNationalPrefixAndCarrierCode(
                    $potentialNationalNumber,
                    $defaultRegionMetadata,
                    $carriercode
                );
                $possibleNumberPattern = $generalDesc->getPossibleNumberPattern();
                if ((preg_match('/^(' . $validNumberPattern . ')$/x', $fullNumber) == 0 &&
                        preg_match('/^(' . $validNumberPattern . ')$/x', $potentialNationalNumber) > 0) ||
                    $this->testNumberLengthAgainstPattern($possibleNumberPattern, (string)$fullNumber)
                    == ValidationResult::TOO_LONG
                ) {
                    $nationalNumber .= $potentialNationalNumber;
                    if ($keepRawInput) {
                        $phoneNumber->setCountryCodeSource(CountryCodeSource::FROM_NUMBER_WITHOUT_PLUS_SIGN);
                    }
                    $phoneNumber->setCountryCode($defaultCountryCode);
                    return $defaultCountryCode;
                }
            }
        }
        $phoneNumber->setCountryCode(0);
        return 0;
    }
    public function maybeStripInternationalPrefixAndNormalize(&$number, $possibleIddPrefix)
    {
        if (mb_strlen($number) == 0) {
            return CountryCodeSource::FROM_DEFAULT_COUNTRY;
        }
        $matches = array();
        $match = preg_match('/^' . self::$PLUS_CHARS_PATTERN . '/' . self::REGEX_FLAGS, $number, $matches, PREG_OFFSET_CAPTURE);
        if ($match > 0) {
            $number = mb_substr($number, $matches[0][1] + mb_strlen($matches[0][0]));
            $number = $this->normalize($number);
            return CountryCodeSource::FROM_NUMBER_WITH_PLUS_SIGN;
        }
        $iddPattern = $possibleIddPrefix;
        $number = $this->normalize($number);
        return $this->parsePrefixAsIdd($iddPattern, $number)
            ? CountryCodeSource::FROM_NUMBER_WITH_IDD
            : CountryCodeSource::FROM_DEFAULT_COUNTRY;
    }
    public static function normalize(&$number)
    {
        $m = new Matcher(self::VALID_ALPHA_PHONE_PATTERN, $number);
        if ($m->matches()) {
            return self::normalizeHelper($number, self::$ALPHA_PHONE_MAPPINGS, true);
        } else {
            return self::normalizeDigitsOnly($number);
        }
    }
    public static function normalizeDigitsOnly($number)
    {
        return self::normalizeDigits($number, false );
    }
    public static function normalizeDigits($number, $keepNonDigits)
    {
        $normalizedDigits = "";
        $numberAsArray = preg_split('/(?<!^)(?!$)/u', $number);
        foreach ($numberAsArray as $character) {
            if (is_numeric($character)) {
                $normalizedDigits .= $character;
            } elseif ($keepNonDigits) {
                $normalizedDigits .= $character;
            }
            if (array_key_exists($character, self::$numericCharacters)) {
                $normalizedDigits .= self::$numericCharacters[$character];
            }
        }
        return $normalizedDigits;
    }
    private function parsePrefixAsIdd($iddPattern, &$number)
    {
        $m = new Matcher($iddPattern, $number);
        if ($m->lookingAt()) {
            $matchEnd = $m->end();
            $digitMatcher = new Matcher(self::$CAPTURING_DIGIT_PATTERN, substr($number, $matchEnd));
            if ($digitMatcher->find()) {
                $normalizedGroup = $this->normalizeDigitsOnly($digitMatcher->group(1));
                if ($normalizedGroup == "0") {
                    return false;
                }
            }
            $number = substr($number, $matchEnd);
            return true;
        }
        return false;
    }
    private function extractCountryCode(&$fullNumber, &$nationalNumber)
    {
        if ((mb_strlen($fullNumber) == 0) || ($fullNumber[0] == '0')) {
            return 0;
        }
        $numberLength = mb_strlen($fullNumber);
        for ($i = 1; $i <= self::MAX_LENGTH_COUNTRY_CODE && $i <= $numberLength; $i++) {
            $potentialCountryCode = (int)substr($fullNumber, 0, $i);
            if (isset($this->countryCallingCodeToRegionCodeMap[$potentialCountryCode])) {
                $nationalNumber .= substr($fullNumber, $i);
                return $potentialCountryCode;
            }
        }
        return 0;
    }
    public function maybeStripNationalPrefixAndCarrierCode(&$number, PhoneMetadata $metadata, &$carrierCode)
    {
        $numberLength = mb_strlen($number);
        $possibleNationalPrefix = $metadata->getNationalPrefixForParsing();
        if ($numberLength == 0 || $possibleNationalPrefix === null || mb_strlen($possibleNationalPrefix) == 0) {
            return false;
        }
        $prefixMatcher = new Matcher($possibleNationalPrefix, $number);
        if ($prefixMatcher->lookingAt()) {
            $nationalNumberRule = $metadata->getGeneralDesc()->getNationalNumberPattern();
            $nationalNumberRuleMatcher = new Matcher($nationalNumberRule, $number);
            $isViableOriginalNumber = $nationalNumberRuleMatcher->matches();
            $numOfGroups = $prefixMatcher->groupCount();
            $transformRule = $metadata->getNationalPrefixTransformRule();
            if ($transformRule === null
                || mb_strlen($transformRule) == 0
                || $prefixMatcher->group($numOfGroups - 1) === null
            ) {
                $matcher = new Matcher($nationalNumberRule, substr($number, $prefixMatcher->end()));
                if ($isViableOriginalNumber && !$matcher->matches()) {
                    return false;
                }
                if ($carrierCode !== null && $numOfGroups > 0 && $prefixMatcher->group($numOfGroups) !== null) {
                    $carrierCode .= $prefixMatcher->group(1);
                }
                $number = substr($number, $prefixMatcher->end());
                return true;
            } else {
                $transformedNumber = $number;
                $transformedNumber = substr_replace(
                    $transformedNumber,
                    $prefixMatcher->replaceFirst($transformRule),
                    0,
                    $numberLength
                );
                $matcher = new Matcher($nationalNumberRule, $transformedNumber);
                if ($isViableOriginalNumber && !$matcher->matches()) {
                    return false;
                }
                if ($carrierCode !== null && $numOfGroups > 1) {
                    $carrierCode .= $prefixMatcher->group(1);
                }
                $number = substr_replace($number, $transformedNumber, 0, mb_strlen($number));
                return true;
            }
        }
        return false;
    }
    private function testNumberLengthAgainstPattern($numberPattern, $number)
    {
        $numberMatcher = new Matcher($numberPattern, $number);
        if ($numberMatcher->matches()) {
            return ValidationResult::IS_POSSIBLE;
        }
        if ($numberMatcher->lookingAt()) {
            return ValidationResult::TOO_LONG;
        } else {
            return ValidationResult::TOO_SHORT;
        }
    }
    public function getRegionCodesForCountryCode($countryCallingCode)
    {
        $regionCodes = isset($this->countryCallingCodeToRegionCodeMap[$countryCallingCode]) ? $this->countryCallingCodeToRegionCodeMap[$countryCallingCode] : null;
        return $regionCodes === null ? array() : $regionCodes;
    }
    public function getCountryCodeForRegion($regionCode)
    {
        if (!$this->isValidRegionCode($regionCode)) {
            return 0;
        }
        return $this->getCountryCodeForValidRegion($regionCode);
    }
    private function getCountryCodeForValidRegion($regionCode)
    {
        $metadata = $this->getMetadataForRegion($regionCode);
        if ($metadata === null) {
            throw new \InvalidArgumentException("Invalid region code: " . $regionCode);
        }
        return $metadata->getCountryCode();
    }
    public function formatNumberForMobileDialing(PhoneNumber $number, $regionCallingFrom, $withFormatting)
    {
        $countryCallingCode = $number->getCountryCode();
        if (!$this->hasValidCountryCallingCode($countryCallingCode)) {
            return $number->hasRawInput() ? $number->getRawInput() : "";
        }
        $formattedNumber = "";
        $numberNoExt = new PhoneNumber();
        $numberNoExt->mergeFrom($number)->clearExtension();
        $regionCode = $this->getRegionCodeForCountryCode($countryCallingCode);
        $numberType = $this->getNumberType($numberNoExt);
        $isValidNumber = ($numberType !== PhoneNumberType::UNKNOWN);
        if ($regionCallingFrom == $regionCode) {
            $isFixedLineOrMobile = ($numberType == PhoneNumberType::FIXED_LINE) || ($numberType == PhoneNumberType::MOBILE) || ($numberType == PhoneNumberType::FIXED_LINE_OR_MOBILE);
            if ($regionCode == "CO" && $numberType == PhoneNumberType::FIXED_LINE) {
                $formattedNumber = $this->formatNationalNumberWithCarrierCode(
                    $numberNoExt,
                    self::COLOMBIA_MOBILE_TO_FIXED_LINE_PREFIX
                );
            } elseif ($regionCode == "BR" && $isFixedLineOrMobile) {
                $formattedNumber = $numberNoExt->hasPreferredDomesticCarrierCode(
                ) ? $this->formatNationalNumberWithCarrierCode($numberNoExt, "") : "";
            } elseif ($isValidNumber && $regionCode == "HU") {
                $formattedNumber = $this->getNddPrefixForRegion(
                        $regionCode,
                        true 
                    ) . " " . $this->format($numberNoExt, PhoneNumberFormat::NATIONAL);
            } elseif ($countryCallingCode === self::NANPA_COUNTRY_CODE) {
                $regionMetadata = $this->getMetadataForRegion($regionCallingFrom);
                if ($this->canBeInternationallyDialled($numberNoExt) &&
                    !$this->isShorterThanPossibleNormalNumber(
                        $regionMetadata,
                        $this->getNationalSignificantNumber($numberNoExt)
                    )
                ) {
                    $formattedNumber = $this->format($numberNoExt, PhoneNumberFormat::INTERNATIONAL);
                } else {
                    $formattedNumber = $this->format($numberNoExt, PhoneNumberFormat::NATIONAL);
                }
            } else {
                if (($regionCode == self::REGION_CODE_FOR_NON_GEO_ENTITY ||
                        (($regionCode == "MX" || $regionCode == "CL") && $isFixedLineOrMobile)) && $this->canBeInternationallyDialled(
                        $numberNoExt
                    )
                ) {
                    $formattedNumber = $this->format($numberNoExt, PhoneNumberFormat::INTERNATIONAL);
                } else {
                    $formattedNumber = $this->format($numberNoExt, PhoneNumberFormat::NATIONAL);
                }
            }
        } elseif ($isValidNumber && $this->canBeInternationallyDialled($numberNoExt)) {
            return $withFormatting ?
                $this->format($numberNoExt, PhoneNumberFormat::INTERNATIONAL) :
                $this->format($numberNoExt, PhoneNumberFormat::E164);
        }
        return $withFormatting ? $formattedNumber : $this->normalizeDiallableCharsOnly($formattedNumber);
    }
    public function formatNationalNumberWithCarrierCode(PhoneNumber $number, $carrierCode)
    {
        $countryCallingCode = $number->getCountryCode();
        $nationalSignificantNumber = $this->getNationalSignificantNumber($number);
        if (!$this->hasValidCountryCallingCode($countryCallingCode)) {
            return $nationalSignificantNumber;
        }
        $regionCode = $this->getRegionCodeForCountryCode($countryCallingCode);
        $metadata = $this->getMetadataForRegionOrCallingCode($countryCallingCode, $regionCode);
        $formattedNumber = $this->formatNsn(
            $nationalSignificantNumber,
            $metadata,
            PhoneNumberFormat::NATIONAL,
            $carrierCode
        );
        $this->maybeAppendFormattedExtension($number, $metadata, PhoneNumberFormat::NATIONAL, $formattedNumber);
        $this->prefixNumberWithCountryCallingCode(
            $countryCallingCode,
            PhoneNumberFormat::NATIONAL,
            $formattedNumber
        );
        return $formattedNumber;
    }
    public function formatNationalNumberWithPreferredCarrierCode(PhoneNumber $number, $fallbackCarrierCode)
    {
        return $this->formatNationalNumberWithCarrierCode(
            $number,
            $number->hasPreferredDomesticCarrierCode()
                ? $number->getPreferredDomesticCarrierCode()
                : $fallbackCarrierCode
        );
    }
    public function canBeInternationallyDialled(PhoneNumber $number)
    {
        $metadata = $this->getMetadataForRegion($this->getRegionCodeForNumber($number));
        if ($metadata === null) {
            return true;
        }
        $nationalSignificantNumber = $this->getNationalSignificantNumber($number);
        return !$this->isNumberMatchingDesc($nationalSignificantNumber, $metadata->getNoInternationalDialling());
    }
    public static function normalizeDiallableCharsOnly($number)
    {
        return self::normalizeHelper($number, self::$DIALLABLE_CHAR_MAPPINGS, true );
    }
    public function formatOutOfCountryKeepingAlphaChars(PhoneNumber $number, $regionCallingFrom)
    {
        $rawInput = $number->getRawInput();
        if (mb_strlen($rawInput) == 0) {
            return $this->formatOutOfCountryCallingNumber($number, $regionCallingFrom);
        }
        $countryCode = $number->getCountryCode();
        if (!$this->hasValidCountryCallingCode($countryCode)) {
            return $rawInput;
        }
        $rawInput = $this->normalizeHelper($rawInput, self::$ALL_PLUS_NUMBER_GROUPING_SYMBOLS, true);
        $nationalNumber = $this->getNationalSignificantNumber($number);
        if (mb_strlen($nationalNumber) > 3) {
            $firstNationalNumberDigit = strpos($rawInput, substr($nationalNumber, 0, 3));
            if ($firstNationalNumberDigit !== false) {
                $rawInput = substr($rawInput, $firstNationalNumberDigit);
            }
        }
        $metadataForRegionCallingFrom = $this->getMetadataForRegion($regionCallingFrom);
        if ($countryCode == self::NANPA_COUNTRY_CODE) {
            if ($this->isNANPACountry($regionCallingFrom)) {
                return $countryCode . " " . $rawInput;
            }
        } else if ($metadataForRegionCallingFrom !== null &&
            $countryCode == $this->getCountryCodeForValidRegion($regionCallingFrom)
        ) {
            $formattingPattern =
                $this->chooseFormattingPatternForNumber(
                    $metadataForRegionCallingFrom->numberFormats(),
                    $nationalNumber
                );
            if ($formattingPattern === null) {
                return $rawInput;
            }
            $newFormat = new NumberFormat();
            $newFormat->mergeFrom($formattingPattern);
            $newFormat->setPattern("(\\d+)(.*)");
            $newFormat->setFormat("$1$2");
            return $this->formatNsnUsingPattern($rawInput, $newFormat, PhoneNumberFormat::NATIONAL);
        }
        $internationalPrefixForFormatting = "";
        if ($metadataForRegionCallingFrom !== null) {
            $internationalPrefix = $metadataForRegionCallingFrom->getInternationalPrefix();
            $uniqueInternationalPrefixMatcher = new Matcher(self::UNIQUE_INTERNATIONAL_PREFIX, $internationalPrefix);
            $internationalPrefixForFormatting =
                $uniqueInternationalPrefixMatcher->matches()
                    ? $internationalPrefix
                    : $metadataForRegionCallingFrom->getPreferredInternationalPrefix();
        }
        $formattedNumber = $rawInput;
        $regionCode = $this->getRegionCodeForCountryCode($countryCode);
        $metadataForRegion = $this->getMetadataForRegionOrCallingCode($countryCode, $regionCode);
        $this->maybeAppendFormattedExtension(
            $number,
            $metadataForRegion,
            PhoneNumberFormat::INTERNATIONAL,
            $formattedNumber
        );
        if (mb_strlen($internationalPrefixForFormatting) > 0) {
            $formattedNumber = $internationalPrefixForFormatting . " " . $countryCode . " " . $formattedNumber;
        } else {
            $this->prefixNumberWithCountryCallingCode(
                $countryCode,
                PhoneNumberFormat::INTERNATIONAL,
                $formattedNumber
            );
        }
        return $formattedNumber;
    }
    public function formatOutOfCountryCallingNumber(PhoneNumber $number, $regionCallingFrom)
    {
        if (!$this->isValidRegionCode($regionCallingFrom)) {
            return $this->format($number, PhoneNumberFormat::INTERNATIONAL);
        }
        $countryCallingCode = $number->getCountryCode();
        $nationalSignificantNumber = $this->getNationalSignificantNumber($number);
        if (!$this->hasValidCountryCallingCode($countryCallingCode)) {
            return $nationalSignificantNumber;
        }
        if ($countryCallingCode == self::NANPA_COUNTRY_CODE) {
            if ($this->isNANPACountry($regionCallingFrom)) {
                return $countryCallingCode . " " . $this->format($number, PhoneNumberFormat::NATIONAL);
            }
        } else if ($countryCallingCode == $this->getCountryCodeForValidRegion($regionCallingFrom)) {
            return $this->format($number, PhoneNumberFormat::NATIONAL);
        }
        $metadataForRegionCallingFrom = $this->getMetadataForRegion($regionCallingFrom);
        $internationalPrefix = $metadataForRegionCallingFrom->getInternationalPrefix();
        $internationalPrefixForFormatting = "";
        $uniqueInternationalPrefixMatcher = new Matcher(self::UNIQUE_INTERNATIONAL_PREFIX, $internationalPrefix);
        if ($uniqueInternationalPrefixMatcher->matches()) {
            $internationalPrefixForFormatting = $internationalPrefix;
        } else if ($metadataForRegionCallingFrom->hasPreferredInternationalPrefix()) {
            $internationalPrefixForFormatting = $metadataForRegionCallingFrom->getPreferredInternationalPrefix();
        }
        $regionCode = $this->getRegionCodeForCountryCode($countryCallingCode);
        $metadataForRegion = $this->getMetadataForRegionOrCallingCode($countryCallingCode, $regionCode);
        $formattedNationalNumber = $this->formatNsn(
            $nationalSignificantNumber,
            $metadataForRegion,
            PhoneNumberFormat::INTERNATIONAL
        );
        $formattedNumber = $formattedNationalNumber;
        $this->maybeAppendFormattedExtension(
            $number,
            $metadataForRegion,
            PhoneNumberFormat::INTERNATIONAL,
            $formattedNumber
        );
        if (mb_strlen($internationalPrefixForFormatting) > 0) {
            $formattedNumber = $internationalPrefixForFormatting . " " . $countryCallingCode . " " . $formattedNumber;
        } else {
            $this->prefixNumberWithCountryCallingCode(
                $countryCallingCode,
                PhoneNumberFormat::INTERNATIONAL,
                $formattedNumber
            );
        }
        return $formattedNumber;
    }
    public function isNANPACountry($regionCode)
    {
        return in_array($regionCode, $this->nanpaRegions);
    }
    public function formatInOriginalFormat(PhoneNumber $number, $regionCallingFrom)
    {
        if ($number->hasRawInput() &&
            ($this->hasUnexpectedItalianLeadingZero($number) || !$this->hasFormattingPatternForNumber($number))
        ) {
            return $number->getRawInput();
        }
        if (!$number->hasCountryCodeSource()) {
            return $this->format($number, PhoneNumberFormat::NATIONAL);
        }
        switch ($number->getCountryCodeSource()) {
            case CountryCodeSource::FROM_NUMBER_WITH_PLUS_SIGN:
                $formattedNumber = $this->format($number, PhoneNumberFormat::INTERNATIONAL);
                break;
            case CountryCodeSource::FROM_NUMBER_WITH_IDD:
                $formattedNumber = $this->formatOutOfCountryCallingNumber($number, $regionCallingFrom);
                break;
            case CountryCodeSource::FROM_NUMBER_WITHOUT_PLUS_SIGN:
                $formattedNumber = substr($this->format($number, PhoneNumberFormat::INTERNATIONAL), 1);
                break;
            case CountryCodeSource::FROM_DEFAULT_COUNTRY:
            default:
                $regionCode = $this->getRegionCodeForCountryCode($number->getCountryCode());
                $nationalPrefix = $this->getNddPrefixForRegion($regionCode, true );
                $nationalFormat = $this->format($number, PhoneNumberFormat::NATIONAL);
                if ($nationalPrefix === null || mb_strlen($nationalPrefix) == 0) {
                    $formattedNumber = $nationalFormat;
                    break;
                }
                if ($this->rawInputContainsNationalPrefix(
                    $number->getRawInput(),
                    $nationalPrefix,
                    $regionCode
                )
                ) {
                    $formattedNumber = $nationalFormat;
                    break;
                }
                $metadata = $this->getMetadataForRegion($regionCode);
                $nationalNumber = $this->getNationalSignificantNumber($number);
                $formatRule = $this->chooseFormattingPatternForNumber($metadata->numberFormats(), $nationalNumber);
                if ($formatRule === null) {
                    $formattedNumber = $nationalFormat;
                    break;
                }
                $candidateNationalPrefixRule = $formatRule->getNationalPrefixFormattingRule();
                $indexOfFirstGroup = strpos($candidateNationalPrefixRule, '$1');
                if ($indexOfFirstGroup <= 0) {
                    $formattedNumber = $nationalFormat;
                    break;
                }
                $candidateNationalPrefixRule = substr($candidateNationalPrefixRule, 0, $indexOfFirstGroup);
                $candidateNationalPrefixRule = $this->normalizeDigitsOnly($candidateNationalPrefixRule);
                if (mb_strlen($candidateNationalPrefixRule) == 0) {
                    $formattedNumber = $nationalFormat;
                    break;
                }
                $numFormatCopy = new NumberFormat();
                $numFormatCopy->mergeFrom($formatRule);
                $numFormatCopy->clearNationalPrefixFormattingRule();
                $numberFormats = array();
                $numberFormats[] = $numFormatCopy;
                $formattedNumber = $this->formatByPattern($number, PhoneNumberFormat::NATIONAL, $numberFormats);
                break;
        }
        $rawInput = $number->getRawInput();
        if ($formattedNumber !== null && mb_strlen($rawInput) > 0) {
            $normalizedFormattedNumber = $this->normalizeDiallableCharsOnly($formattedNumber);
            $normalizedRawInput = $this->normalizeDiallableCharsOnly($rawInput);
            if ($normalizedFormattedNumber != $normalizedRawInput) {
                $formattedNumber = $rawInput;
            }
        }
        return $formattedNumber;
    }
    private function hasUnexpectedItalianLeadingZero(PhoneNumber $number)
    {
        return $number->isItalianLeadingZero() && !$this->isLeadingZeroPossible($number->getCountryCode());
    }
    public function isLeadingZeroPossible($countryCallingCode)
    {
        $mainMetadataForCallingCode = $this->getMetadataForRegionOrCallingCode(
            $countryCallingCode,
            $this->getRegionCodeForCountryCode($countryCallingCode)
        );
        if ($mainMetadataForCallingCode === null) {
            return false;
        }
        return (bool)$mainMetadataForCallingCode->isLeadingZeroPossible();
    }
    private function hasFormattingPatternForNumber(PhoneNumber $number)
    {
        $countryCallingCode = $number->getCountryCode();
        $phoneNumberRegion = $this->getRegionCodeForCountryCode($countryCallingCode);
        $metadata = $this->getMetadataForRegionOrCallingCode($countryCallingCode, $phoneNumberRegion);
        if ($metadata === null) {
            return false;
        }
        $nationalNumber = $this->getNationalSignificantNumber($number);
        $formatRule = $this->chooseFormattingPatternForNumber($metadata->numberFormats(), $nationalNumber);
        return $formatRule !== null;
    }
    public function getNddPrefixForRegion($regionCode, $stripNonDigits)
    {
        $metadata = $this->getMetadataForRegion($regionCode);
        if ($metadata === null) {
            return null;
        }
        $nationalPrefix = $metadata->getNationalPrefix();
        if (mb_strlen($nationalPrefix) == 0) {
            return null;
        }
        if ($stripNonDigits) {
            $nationalPrefix = str_replace("~", "", $nationalPrefix);
        }
        return $nationalPrefix;
    }
    private function rawInputContainsNationalPrefix($rawInput, $nationalPrefix, $regionCode)
    {
        $normalizedNationalNumber = $this->normalizeDigitsOnly($rawInput);
        if (strpos($normalizedNationalNumber, $nationalPrefix) === 0) {
            try {
                return $this->isValidNumber(
                    $this->parse(substr($normalizedNationalNumber, mb_strlen($nationalPrefix)), $regionCode)
                );
            } catch (NumberParseException $e) {
                return false;
            }
        }
        return false;
    }
    public function isValidNumber(PhoneNumber $number)
    {
        $regionCode = $this->getRegionCodeForNumber($number);
        return $this->isValidNumberForRegion($number, $regionCode);
    }
    public function isValidNumberForRegion(PhoneNumber $number, $regionCode)
    {
        $countryCode = $number->getCountryCode();
        $metadata = $this->getMetadataForRegionOrCallingCode($countryCode, $regionCode);
        if (($metadata === null) ||
            (self::REGION_CODE_FOR_NON_GEO_ENTITY !== $regionCode &&
                $countryCode !== $this->getCountryCodeForValidRegion($regionCode))
        ) {
            return false;
        }
        $nationalSignificantNumber = $this->getNationalSignificantNumber($number);
        return $this->getNumberTypeHelper($nationalSignificantNumber, $metadata) != PhoneNumberType::UNKNOWN;
    }
    public function parse($numberToParse, $defaultRegion, PhoneNumber $phoneNumber = null, $keepRawInput = false)
    {
        if ($phoneNumber === null) {
            $phoneNumber = new PhoneNumber();
        }
        $this->parseHelper($numberToParse, $defaultRegion, $keepRawInput, true, $phoneNumber);
        return $phoneNumber;
    }
    public function formatByPattern(PhoneNumber $number, $numberFormat, array $userDefinedFormats)
    {
        $countryCallingCode = $number->getCountryCode();
        $nationalSignificantNumber = $this->getNationalSignificantNumber($number);
        if (!$this->hasValidCountryCallingCode($countryCallingCode)) {
            return $nationalSignificantNumber;
        }
        $regionCode = $this->getRegionCodeForCountryCode($countryCallingCode);
        $metadata = $this->getMetadataForRegionOrCallingCode($countryCallingCode, $regionCode);
        $formattedNumber = "";
        $formattingPattern = $this->chooseFormattingPatternForNumber($userDefinedFormats, $nationalSignificantNumber);
        if ($formattingPattern === null) {
            $formattedNumber .= $nationalSignificantNumber;
        } else {
            $numFormatCopy = new NumberFormat();
            $numFormatCopy->mergeFrom($formattingPattern);
            $nationalPrefixFormattingRule = $formattingPattern->getNationalPrefixFormattingRule();
            if (mb_strlen($nationalPrefixFormattingRule) > 0) {
                $nationalPrefix = $metadata->getNationalPrefix();
                if (mb_strlen($nationalPrefix) > 0) {
                    $npPatternMatcher = new Matcher(self::NP_PATTERN, $nationalPrefixFormattingRule);
                    $nationalPrefixFormattingRule = $npPatternMatcher->replaceFirst($nationalPrefix);
                    $fgPatternMatcher = new Matcher(self::FG_PATTERN, $nationalPrefixFormattingRule);
                    $nationalPrefixFormattingRule = $fgPatternMatcher->replaceFirst("\\$1");
                    $numFormatCopy->setNationalPrefixFormattingRule($nationalPrefixFormattingRule);
                } else {
                    $numFormatCopy->clearNationalPrefixFormattingRule();
                }
            }
            $formattedNumber .= $this->formatNsnUsingPattern($nationalSignificantNumber, $numFormatCopy, $numberFormat);
        }
        $this->maybeAppendFormattedExtension($number, $metadata, $numberFormat, $formattedNumber);
        $this->prefixNumberWithCountryCallingCode($countryCallingCode, $numberFormat, $formattedNumber);
        return $formattedNumber;
    }
    public function getExampleNumber($regionCode)
    {
        return $this->getExampleNumberForType($regionCode, PhoneNumberType::FIXED_LINE);
    }
    public function getExampleNumberForType($regionCode, $type)
    {
        if (!$this->isValidRegionCode($regionCode)) {
            return null;
        }
        $desc = $this->getNumberDescByType($this->getMetadataForRegion($regionCode), $type);
        try {
            if ($desc->hasExampleNumber()) {
                return $this->parse($desc->getExampleNumber(), $regionCode);
            }
        } catch (NumberParseException $e) {
        }
        return null;
    }
    private function getNumberDescByType(PhoneMetadata $metadata, $type)
    {
        switch ($type) {
            case PhoneNumberType::PREMIUM_RATE:
                return $metadata->getPremiumRate();
            case PhoneNumberType::TOLL_FREE:
                return $metadata->getTollFree();
            case PhoneNumberType::MOBILE:
                return $metadata->getMobile();
            case PhoneNumberType::FIXED_LINE:
            case PhoneNumberType::FIXED_LINE_OR_MOBILE:
                return $metadata->getFixedLine();
            case PhoneNumberType::SHARED_COST:
                return $metadata->getSharedCost();
            case PhoneNumberType::VOIP:
                return $metadata->getVoip();
            case PhoneNumberType::PERSONAL_NUMBER:
                return $metadata->getPersonalNumber();
            case PhoneNumberType::PAGER:
                return $metadata->getPager();
            case PhoneNumberType::UAN:
                return $metadata->getUan();
            case PhoneNumberType::VOICEMAIL:
                return $metadata->getVoicemail();
            default:
                return $metadata->getGeneralDesc();
        }
    }
    public function getExampleNumberForNonGeoEntity($countryCallingCode)
    {
        $metadata = $this->getMetadataForNonGeographicalRegion($countryCallingCode);
        if ($metadata !== null) {
            $desc = $metadata->getGeneralDesc();
            try {
                if ($desc->hasExampleNumber()) {
                    return $this->parse("+" . $countryCallingCode . $desc->getExampleNumber(), "ZZ");
                }
            } catch (NumberParseException $e) {
            }
        }
        return null;
    }
    public function isNumberMatch($firstNumberIn, $secondNumberIn)
    {
        if (is_string($firstNumberIn) && is_string($secondNumberIn)) {
            try {
                $firstNumberAsProto = $this->parse($firstNumberIn, self::UNKNOWN_REGION);
                return $this->isNumberMatch($firstNumberAsProto, $secondNumberIn);
            } catch (NumberParseException $e) {
                if ($e->getErrorType() === NumberParseException::INVALID_COUNTRY_CODE) {
                    try {
                        $secondNumberAsProto = $this->parse($secondNumberIn, self::UNKNOWN_REGION);
                        return $this->isNumberMatch($secondNumberAsProto, $firstNumberIn);
                    } catch (NumberParseException $e2) {
                        if ($e2->getErrorType() === NumberParseException::INVALID_COUNTRY_CODE) {
                            try {
                                $firstNumberProto = new PhoneNumber();
                                $secondNumberProto = new PhoneNumber();
                                $this->parseHelper($firstNumberIn, null, false, false, $firstNumberProto);
                                $this->parseHelper($secondNumberIn, null, false, false, $secondNumberProto);
                                return $this->isNumberMatch($firstNumberProto, $secondNumberProto);
                            } catch (NumberParseException $e3) {
                            }
                        }
                    }
                }
            }
            return MatchType::NOT_A_NUMBER;
        }
        if ($firstNumberIn instanceof PhoneNumber && is_string($secondNumberIn)) {
            try {
                $secondNumberAsProto = $this->parse($secondNumberIn, self::UNKNOWN_REGION);
                return $this->isNumberMatch($firstNumberIn, $secondNumberAsProto);
            } catch (NumberParseException $e) {
                if ($e->getErrorType() === NumberParseException::INVALID_COUNTRY_CODE) {
                    $firstNumberRegion = $this->getRegionCodeForCountryCode($firstNumberIn->getCountryCode());
                    try {
                        if ($firstNumberRegion != self::UNKNOWN_REGION) {
                            $secondNumberWithFirstNumberRegion = $this->parse($secondNumberIn, $firstNumberRegion);
                            $match = $this->isNumberMatch($firstNumberIn, $secondNumberWithFirstNumberRegion);
                            if ($match === MatchType::EXACT_MATCH) {
                                return MatchType::NSN_MATCH;
                            }
                            return $match;
                        } else {
                            $secondNumberProto = new PhoneNumber();
                            $this->parseHelper($secondNumberIn, null, false, false, $secondNumberProto);
                            return $this->isNumberMatch($firstNumberIn, $secondNumberProto);
                        }
                    } catch (NumberParseException $e2) {
                    }
                }
            }
        }
        if ($firstNumberIn instanceof PhoneNumber && $secondNumberIn instanceof PhoneNumber) {
            $firstNumber = new PhoneNumber();
            $firstNumber->mergeFrom($firstNumberIn);
            $secondNumber = new PhoneNumber();
            $secondNumber->mergeFrom($secondNumberIn);
            $firstNumber->clearRawInput();
            $firstNumber->clearCountryCodeSource();
            $firstNumber->clearPreferredDomesticCarrierCode();
            $secondNumber->clearRawInput();
            $secondNumber->clearCountryCodeSource();
            $secondNumber->clearPreferredDomesticCarrierCode();
            if ($firstNumber->hasExtension() && mb_strlen($firstNumber->getExtension()) === 0) {
                $firstNumber->clearExtension();
            }
            if ($secondNumber->hasExtension() && mb_strlen($secondNumber->getExtension()) === 0) {
                $secondNumber->clearExtension();
            }
            if ($firstNumber->hasExtension() && $secondNumber->hasExtension() &&
                $firstNumber->getExtension() != $secondNumber->getExtension()
            ) {
                return MatchType::NO_MATCH;
            }
            $firstNumberCountryCode = $firstNumber->getCountryCode();
            $secondNumberCountryCode = $secondNumber->getCountryCode();
            if ($firstNumberCountryCode != 0 && $secondNumberCountryCode != 0) {
                if ($firstNumber->equals($secondNumber)) {
                    return MatchType::EXACT_MATCH;
                } elseif ($firstNumberCountryCode == $secondNumberCountryCode &&
                    $this->isNationalNumberSuffixOfTheOther($firstNumber, $secondNumber)
                ) {
                    return MatchType::SHORT_NSN_MATCH;
                }
                return MatchType::NO_MATCH;
            }
            $firstNumber->setCountryCode($secondNumberCountryCode);
            if ($firstNumber->equals($secondNumber)) {
                return MatchType::NSN_MATCH;
            }
            if ($this->isNationalNumberSuffixOfTheOther($firstNumber, $secondNumber)) {
                return MatchType::SHORT_NSN_MATCH;
            }
            return MatchType::NO_MATCH;
        }
        return MatchType::NOT_A_NUMBER;
    }
    private function isNationalNumberSuffixOfTheOther(PhoneNumber $firstNumber, PhoneNumber $secondNumber)
    {
        $firstNumberNationalNumber = trim((string)$firstNumber->getNationalNumber());
        $secondNumberNationalNumber = trim((string)$secondNumber->getNationalNumber());
        return $this->stringEndsWithString($firstNumberNationalNumber, $secondNumberNationalNumber) ||
        $this->stringEndsWithString($secondNumberNationalNumber, $firstNumberNationalNumber);
    }
    private function stringEndsWithString($hayStack, $needle)
    {
        $revNeedle = strrev($needle);
        $revHayStack = strrev($hayStack);
        return strpos($revHayStack, $revNeedle) === 0;
    }
    public function isMobileNumberPortableRegion($regionCode)
    {
        $metadata = $this->getMetadataForRegion($regionCode);
        if ($metadata === null) {
            return false;
        }
        return $metadata->isMobileNumberPortableRegion();
    }
    public function isPossibleNumber($number, $regionDialingFrom = null)
    {
        if ($regionDialingFrom !== null && is_string($number)) {
            try {
                return $this->isPossibleNumberWithReason(
                    $this->parse($number, $regionDialingFrom)
                ) === ValidationResult::IS_POSSIBLE;
            } catch (NumberParseException $e) {
                return false;
            }
        } else {
            return $this->isPossibleNumberWithReason($number) === ValidationResult::IS_POSSIBLE;
        }
    }
    public function isPossibleNumberWithReason(PhoneNumber $number)
    {
        $nationalNumber = $this->getNationalSignificantNumber($number);
        $countryCode = $number->getCountryCode();
        if (!$this->hasValidCountryCallingCode($countryCode)) {
            return ValidationResult::INVALID_COUNTRY_CODE;
        }
        $regionCode = $this->getRegionCodeForCountryCode($countryCode);
        $metadata = $this->getMetadataForRegionOrCallingCode($countryCode, $regionCode);
        $possibleNumberPattern = $metadata->getGeneralDesc()->getPossibleNumberPattern();
        return $this->testNumberLengthAgainstPattern($possibleNumberPattern, $nationalNumber);
    }
    public function truncateTooLongNumber(PhoneNumber $number)
    {
        if ($this->isValidNumber($number)) {
            return true;
        }
        $numberCopy = new PhoneNumber();
        $numberCopy->mergeFrom($number);
        $nationalNumber = $number->getNationalNumber();
        do {
            $nationalNumber = floor($nationalNumber / 10);
            $numberCopy->setNationalNumber($nationalNumber);
            if ($this->isPossibleNumberWithReason($numberCopy) == ValidationResult::TOO_SHORT || $nationalNumber == 0) {
                return false;
            }
        } while (!$this->isValidNumber($numberCopy));
        $number->setNationalNumber($nationalNumber);
        return true;
    }
}
