<?php
namespace libphonenumber\buildtools;
use libphonenumber\NumberFormat;
use libphonenumber\PhoneMetadata;
use libphonenumber\PhoneNumberDesc;
class BuildMetadataFromXml
{
    const CARRIER_CODE_FORMATTING_RULE = "carrierCodeFormattingRule";
    const COUNTRY_CODE = "countryCode";
    const EMERGENCY = "emergency";
    const EXAMPLE_NUMBER = "exampleNumber";
    const FIXED_LINE = "fixedLine";
    const FORMAT = "format";
    const GENERAL_DESC = "generalDesc";
    const INTERNATIONAL_PREFIX = "internationalPrefix";
    const INTL_FORMAT = "intlFormat";
    const LEADING_DIGITS = "leadingDigits";
    const LEADING_ZERO_POSSIBLE = "leadingZeroPossible";
    const MOBILE_NUMBER_PORTABLE_REGION = "mobileNumberPortableRegion";
    const MAIN_COUNTRY_FOR_CODE = "mainCountryForCode";
    const MOBILE = "mobile";
    const NATIONAL_NUMBER_PATTERN = "nationalNumberPattern";
    const NATIONAL_PREFIX = "nationalPrefix";
    const NATIONAL_PREFIX_FORMATTING_RULE = "nationalPrefixFormattingRule";
    const NATIONAL_PREFIX_OPTIONAL_WHEN_FORMATTING = "nationalPrefixOptionalWhenFormatting";
    const NATIONAL_PREFIX_FOR_PARSING = "nationalPrefixForParsing";
    const NATIONAL_PREFIX_TRANSFORM_RULE = "nationalPrefixTransformRule";
    const NO_INTERNATIONAL_DIALLING = "noInternationalDialling";
    const NUMBER_FORMAT = "numberFormat";
    const PAGER = "pager";
    const CARRIER_SPECIFIC = 'carrierSpecific';
    const PATTERN = "pattern";
    const PERSONAL_NUMBER = "personalNumber";
    const POSSIBLE_NUMBER_PATTERN = "possibleNumberPattern";
    const PREFERRED_EXTN_PREFIX = "preferredExtnPrefix";
    const PREFERRED_INTERNATIONAL_PREFIX = "preferredInternationalPrefix";
    const PREMIUM_RATE = "premiumRate";
    const SHARED_COST = "sharedCost";
    const SHORT_CODE = "shortCode";
    const STANDARD_RATE = "standardRate";
    const TOLL_FREE = "tollFree";
    const UAN = "uan";
    const VOICEMAIL = "voicemail";
    const VOIP = "voip";
    private static $liteBuild;
    public static function buildPhoneMetadataCollection($inputXmlFile, $liteBuild)
    {
        self::$liteBuild = $liteBuild;
        $document = new \DOMDocument();
        $document->load($inputXmlFile);
        $territories = $document->getElementsByTagName("territory");
        $metadataCollection = array();
        foreach ($territories as $territory) {
            if ($territory->hasAttribute("id")) {
                $regionCode = $territory->getAttribute("id");
            } else {
                $regionCode = "";
            }
            $metadata = self::loadCountryMetadata($regionCode, $territory);
            $metadataCollection[] = $metadata;
        }
        return $metadataCollection;
    }
    public static function loadCountryMetadata($regionCode, \DOMElement $element)
    {
        $nationalPrefix = self::getNationalPrefix($element);
        $nationalPrefixFormattingRule = self::getNationalPrefixFormattingRuleFromElement($element, $nationalPrefix);
        $metadata = self::loadTerritoryTagMetadata(
            $regionCode,
            $element,
            $nationalPrefix,
            $nationalPrefixFormattingRule
        );
        self::loadAvailableFormats($metadata, $regionCode, $element, $nationalPrefix, $nationalPrefixFormattingRule);
        self::loadGeneralDesc($metadata, $element);
        return $metadata;
    }
    private static function getNationalPrefix(\DOMElement $element)
    {
        return $element->hasAttribute(self::NATIONAL_PREFIX) ? $element->getAttribute(self::NATIONAL_PREFIX) : "";
    }
    private static function getNationalPrefixFormattingRuleFromElement(\DOMElement $element, $nationalPrefix)
    {
        $nationalPrefixFormattingRule = $element->getAttribute(self::NATIONAL_PREFIX_FORMATTING_RULE);
        $nationalPrefixFormattingRule = str_replace('$NP', $nationalPrefix, $nationalPrefixFormattingRule);
        $nationalPrefixFormattingRule = str_replace('$FG', '$1', $nationalPrefixFormattingRule);
        return $nationalPrefixFormattingRule;
    }
    private static function loadTerritoryTagMetadata(
        $regionCode,
        \DOMElement $element,
        $nationalPrefix,
        $nationalPrefixFormattingRule
    ) {
        $metadata = new PhoneMetadata();
        $metadata->setId($regionCode);
        $metadata->setCountryCode((int)$element->getAttribute(self::COUNTRY_CODE));
        if ($element->hasAttribute(self::LEADING_DIGITS)) {
            $metadata->setLeadingDigits($element->getAttribute(self::LEADING_DIGITS));
        }
        $metadata->setInternationalPrefix($element->getAttribute(self::INTERNATIONAL_PREFIX));
        if ($element->hasAttribute(self::PREFERRED_INTERNATIONAL_PREFIX)) {
            $preferredInternationalPrefix = $element->getAttribute(self::PREFERRED_INTERNATIONAL_PREFIX);
            $metadata->setPreferredInternationalPrefix($preferredInternationalPrefix);
        }
        if ($element->hasAttribute(self::NATIONAL_PREFIX_FOR_PARSING)) {
            $metadata->setNationalPrefixForParsing(
                $element->getAttribute(self::NATIONAL_PREFIX_FOR_PARSING)
            );
            if ($element->hasAttribute(self::NATIONAL_PREFIX_TRANSFORM_RULE)) {
                $metadata->setNationalPrefixTransformRule($element->getAttribute(self::NATIONAL_PREFIX_TRANSFORM_RULE));
            }
        }
        if ($nationalPrefix != '') {
            $metadata->setNationalPrefix($nationalPrefix);
            if (!$metadata->hasNationalPrefixForParsing()) {
                $metadata->setNationalPrefixForParsing($nationalPrefix);
            }
        }
        if ($element->hasAttribute(self::PREFERRED_EXTN_PREFIX)) {
            $metadata->setPreferredExtnPrefix($element->getAttribute(self::PREFERRED_EXTN_PREFIX));
        }
        if ($element->hasAttribute(self::MAIN_COUNTRY_FOR_CODE)) {
            $metadata->setMainCountryForCode(true);
        }
        if ($element->hasAttribute(self::LEADING_ZERO_POSSIBLE)) {
            $metadata->setLeadingZeroPossible(true);
        }
        if ($element->hasAttribute(self::MOBILE_NUMBER_PORTABLE_REGION)) {
            $metadata->setMobileNumberPortableRegion(true);
        }
        return $metadata;
    }
    private static function loadAvailableFormats(
        PhoneMetadata $metadata,
        $regionCode,
        \DOMElement $element,
        $nationalPrefix,
        $nationalPrefixFormattingRule
    ) {
        $carrierCodeFormattingRule = "";
        if ($element->hasAttribute(self::CARRIER_CODE_FORMATTING_RULE)) {
            $carrierCodeFormattingRule = self::getDomesticCarrierCodeFormattingRuleFromElement(
                $element,
                $nationalPrefix
            );
        }
        $numberFormatElements = $element->getElementsByTagName(self::NUMBER_FORMAT);
        $hasExplicitIntlFormatDefined = false;
        $numOfFormatElements = $numberFormatElements->length;
        if ($numOfFormatElements > 0) {
            for ($i = 0; $i < $numOfFormatElements; $i++) {
                $numberFormatElement = $numberFormatElements->item($i);
                $format = new NumberFormat();
                if ($numberFormatElement->hasAttribute(self::NATIONAL_PREFIX_FORMATTING_RULE)) {
                    $format->setNationalPrefixFormattingRule(
                        self::getNationalPrefixFormattingRuleFromElement($numberFormatElement, $nationalPrefix)
                    );
                } else {
                    $format->setNationalPrefixFormattingRule($nationalPrefixFormattingRule);
                }
                if ($numberFormatElement->hasAttribute(self::CARRIER_CODE_FORMATTING_RULE)) {
                    $format->setDomesticCarrierCodeFormattingRule(
                        self::getDomesticCarrierCodeFormattingRuleFromElement($numberFormatElement, $nationalPrefix)
                    );
                } else {
                    $format->setDomesticCarrierCodeFormattingRule($carrierCodeFormattingRule);
                }
                self::loadNationalFormat($metadata, $numberFormatElement, $format);
                $metadata->addNumberFormat($format);
                if (self::loadInternationalFormat($metadata, $numberFormatElement, $format)) {
                    $hasExplicitIntlFormatDefined = true;
                }
            }
            if (!$hasExplicitIntlFormatDefined) {
                $metadata->clearIntlNumberFormat();
            }
        }
    }
    private static function getDomesticCarrierCodeFormattingRuleFromElement(\DOMElement $element, $nationalPrefix)
    {
        $carrierCodeFormattingRule = $element->getAttribute(self::CARRIER_CODE_FORMATTING_RULE);
        $carrierCodeFormattingRule = str_replace('$NP', $nationalPrefix, $carrierCodeFormattingRule);
        $carrierCodeFormattingRule = str_replace('$FG', '$1', $carrierCodeFormattingRule);
        return $carrierCodeFormattingRule;
    }
    private static function loadNationalFormat(
        PhoneMetadata $metadata,
        \DOMElement $numberFormatElement,
        NumberFormat $format
    ) {
        self::setLeadingDigitsPatterns($numberFormatElement, $format);
        $format->setPattern($numberFormatElement->getAttribute(self::PATTERN));
        $formatPattern = $numberFormatElement->getElementsByTagName(self::FORMAT);
        if ($formatPattern->length != 1) {
            $countryId = strlen($metadata->getId()) > 0 ? $metadata->getId() : $metadata->getCountryCode();
            throw new \RuntimeException("Invalid number of format patterns for country: " . $countryId);
        }
        $nationalFormat = $formatPattern->item(0)->firstChild->nodeValue;
        $format->setFormat($nationalFormat);
    }
    public static function setLeadingDigitsPatterns(\DOMElement $numberFormatElement, NumberFormat $format)
    {
        $leadingDigitsPatternNodes = $numberFormatElement->getElementsByTagName(self::LEADING_DIGITS);
        $numOfLeadingDigitsPatterns = $leadingDigitsPatternNodes->length;
        if ($numOfLeadingDigitsPatterns > 0) {
            for ($i = 0; $i < $numOfLeadingDigitsPatterns; $i++) {
                $elt = $leadingDigitsPatternNodes->item($i);
                $format->addLeadingDigitsPattern(
                    $elt->firstChild->nodeValue,
                    true
                );
            }
        }
    }
    private static function loadInternationalFormat(
        PhoneMetadata $metadata,
        \DOMElement $numberFormatElement,
        NumberFormat $nationalFormat
    ) {
        $intlFormat = new NumberFormat();
        $intlFormatPattern = $numberFormatElement->getElementsByTagName(self::INTL_FORMAT);
        $hasExplicitIntlFormatDefined = false;
        if ($intlFormatPattern->length > 1) {
            $countryId = strlen($metadata->getId()) > 0 ? $metadata->getId() : $metadata->getCountryCode();
            throw new \RuntimeException("Invalid number of intlFormat patterns for country: " . $countryId);
        } elseif ($intlFormatPattern->length == 0) {
            $intlFormat->mergeFrom($nationalFormat);
        } else {
            $intlFormat->setPattern($numberFormatElement->getAttribute(self::PATTERN));
            self::setLeadingDigitsPatterns($numberFormatElement, $intlFormat);
            $intlFormatPatternValue = $intlFormatPattern->item(0)->firstChild->nodeValue;
            if ($intlFormatPatternValue !== "NA") {
                $intlFormat->setFormat($intlFormatPatternValue);
            }
            $hasExplicitIntlFormatDefined = true;
        }
        if ($intlFormat->hasFormat()) {
            $metadata->addIntlNumberFormat($intlFormat);
        }
        return $hasExplicitIntlFormatDefined;
    }
    private static function loadGeneralDesc(PhoneMetadata $metadata, \DOMElement $element)
    {
        $generalDesc = new PhoneNumberDesc();
        $generalDesc = self::processPhoneNumberDescElement($generalDesc, $element, self::GENERAL_DESC);
        $metadata->setGeneralDesc($generalDesc);
        $metadata->setFixedLine(self::processPhoneNumberDescElement($generalDesc, $element, self::FIXED_LINE));
        $metadata->setMobile(self::processPhoneNumberDescElement($generalDesc, $element, self::MOBILE));
        $metadata->setStandardRate(self::processPhoneNumberDescElement($generalDesc, $element, self::STANDARD_RATE));
        $metadata->setPremiumRate(self::processPhoneNumberDescElement($generalDesc, $element, self::PREMIUM_RATE));
        $metadata->setShortCode(self::processPhoneNumberDescElement($generalDesc, $element, self::SHORT_CODE));
        $metadata->setTollFree(self::processPhoneNumberDescElement($generalDesc, $element, self::TOLL_FREE));
        $metadata->setSharedCost(self::processPhoneNumberDescElement($generalDesc, $element, self::SHARED_COST));
        $metadata->setVoip(self::processPhoneNumberDescElement($generalDesc, $element, self::VOIP));
        $metadata->setPersonalNumber(
            self::processPhoneNumberDescElement($generalDesc, $element, self::PERSONAL_NUMBER)
        );
        $metadata->setPager(self::processPhoneNumberDescElement($generalDesc, $element, self::PAGER));
        $metadata->setUan(self::processPhoneNumberDescElement($generalDesc, $element, self::UAN));
        $metadata->setEmergency(self::processPhoneNumberDescElement($generalDesc, $element, self::EMERGENCY));
        $metadata->setVoicemail(self::processPhoneNumberDescElement($generalDesc, $element, self::VOICEMAIL));
        $metadata->setCarrierSpecific(
            self::processPhoneNumberDescElement($generalDesc, $element, self::CARRIER_SPECIFIC)
        );
        $metadata->setNoInternationalDialling(
            self::processPhoneNumberDescElement($generalDesc, $element, self::NO_INTERNATIONAL_DIALLING)
        );
        $metadata->setSameMobileAndFixedLinePattern(
            $metadata->getMobile()->getNationalNumberPattern() === $metadata->getFixedLine()->getNationalNumberPattern()
        );
    }
    private static function processPhoneNumberDescElement(
        PhoneNumberDesc $generalDesc,
        \DOMElement $countryElement,
        $numberType
    ) {
        $phoneNumberDescList = $countryElement->getElementsByTagName($numberType);
        $numberDesc = new PhoneNumberDesc();
        if ($phoneNumberDescList->length == 0 && !self::isValidNumberType($numberType)) {
            $numberDesc->setNationalNumberPattern("NA");
            $numberDesc->setPossibleNumberPattern("NA");
            return $numberDesc;
        }
        $numberDesc->mergeFrom($generalDesc);
        if ($phoneNumberDescList->length > 0) {
            $element = $phoneNumberDescList->item(0);
            $possiblePattern = $element->getElementsByTagName(self::POSSIBLE_NUMBER_PATTERN);
            if ($possiblePattern->length > 0) {
                $numberDesc->setPossibleNumberPattern($possiblePattern->item(0)->firstChild->nodeValue);
            }
            $validPattern = $element->getElementsByTagName(self::NATIONAL_NUMBER_PATTERN);
            if ($validPattern->length > 0) {
                $numberDesc->setNationalNumberPattern($validPattern->item(0)->firstChild->nodeValue);
            }
            if (!self::$liteBuild) {
                $exampleNumber = $element->getElementsByTagName(self::EXAMPLE_NUMBER);
                if ($exampleNumber->length > 0) {
                    $numberDesc->setExampleNumber($exampleNumber->item(0)->firstChild->nodeValue);
                }
            }
        }
        return $numberDesc;
    }
    private static function isValidNumberType($numberType)
    {
        return $numberType == self::FIXED_LINE || $numberType == self::MOBILE || $numberType == self::GENERAL_DESC;
    }
    public static function buildCountryCodeToRegionCodeMap($metadataCollection)
    {
        $countryCodeToRegionCodeMap = array();
        foreach ($metadataCollection as $metadata) {
            $regionCode = $metadata->getId();
            $countryCode = $metadata->getCountryCode();
            if (array_key_exists($countryCode, $countryCodeToRegionCodeMap)) {
                if ($metadata->getMainCountryForCode()) {
                    array_unshift($countryCodeToRegionCodeMap[$countryCode], $regionCode);
                } else {
                    $countryCodeToRegionCodeMap[$countryCode][] = $regionCode;
                }
            } else {
                $listWithRegionCode = array();
                if ($regionCode != '') { 
                    $listWithRegionCode[] = $regionCode;
                }
                $countryCodeToRegionCodeMap[$countryCode] = $listWithRegionCode;
            }
        }
        return $countryCodeToRegionCodeMap;
    }
}
