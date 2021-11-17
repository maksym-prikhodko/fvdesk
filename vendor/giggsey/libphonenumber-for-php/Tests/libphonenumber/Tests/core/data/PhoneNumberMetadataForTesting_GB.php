<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '\\d{10}',
    'PossibleNumberPattern' => '\\d{6,10}',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '[1-6]\\d{9}',
    'PossibleNumberPattern' => '\\d{6,10}',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '7[1-57-9]\\d{8}',
    'PossibleNumberPattern' => '\\d{10}',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => '80\\d{8}',
    'PossibleNumberPattern' => '\\d{10}',
  ),
  'premiumRate' => 
  array (
    'NationalNumberPattern' => '9[018]\\d{8}',
    'PossibleNumberPattern' => '\\d{10}',
  ),
  'sharedCost' => 
  array (
    'NationalNumberPattern' => '8(?:4[3-5]|7[0-2])\\d{7}',
    'PossibleNumberPattern' => '\\d{10}',
  ),
  'personalNumber' => 
  array (
    'NationalNumberPattern' => '70\\d{8}',
    'PossibleNumberPattern' => '\\d{10}',
  ),
  'voip' => 
  array (
    'NationalNumberPattern' => '56\\d{8}',
    'PossibleNumberPattern' => '\\d{10}',
  ),
  'pager' => 
  array (
    'NationalNumberPattern' => '76\\d{8}',
    'PossibleNumberPattern' => '\\d{10}',
  ),
  'uan' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'emergency' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'voicemail' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'shortCode' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'standardRate' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'carrierSpecific' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'noInternationalDialling' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'id' => 'GB',
  'countryCode' => 44,
  'internationalPrefix' => '00',
  'nationalPrefix' => '0',
  'nationalPrefixForParsing' => '0',
  'sameMobileAndFixedLinePattern' => false,
  'numberFormat' => 
  array (
    0 => 
    array (
      'pattern' => '(\\d{2})(\\d{4})(\\d{4})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '[1-59]|[78]0',
      ),
      'nationalPrefixFormattingRule' => '(0$1)',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    1 => 
    array (
      'pattern' => '(\\d)(\\d{3})(\\d{3})(\\d{3})',
      'format' => '$1 $2 $3 $4',
      'leadingDigitsPatterns' => 
      array (
        0 => '6',
      ),
      'nationalPrefixFormattingRule' => '(0$1)',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    2 => 
    array (
      'pattern' => '(\\d{4})(\\d{3})(\\d{3})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '7[1-57-9]',
      ),
      'nationalPrefixFormattingRule' => '(0$1)',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    3 => 
    array (
      'pattern' => '(\\d{3})(\\d{3})(\\d{4})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '8[47]',
      ),
      'nationalPrefixFormattingRule' => '(0$1)',
      'domesticCarrierCodeFormattingRule' => '',
    ),
  ),
  'intlNumberFormat' => 
  array (
  ),
  'mainCountryForCode' => false,
  'leadingZeroPossible' => false,
  'mobileNumberPortableRegion' => true,
);
