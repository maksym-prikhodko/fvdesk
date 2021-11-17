<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '[1-9]\\d{4,6}',
    'PossibleNumberPattern' => '\\d{5,7}',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '
          (?:
            1[4-79]|
            [23]\\d|
            4[01]|
            5[03]|
            6[0-37]
          )\\d{3}
        ',
    'PossibleNumberPattern' => '\\d{5}',
    'ExampleNumber' => '40123',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '
          48\\d{3}|
          7(?:
            30|
            [46-8]\\d|
            5[025-9]|
            9[0-5]
          )\\d{4}|
          8[4-8]\\d{5}|
          9(?:
            1[2-9]|
            2[013-9]|
            3[0-2]|
            [46]\\d|
            5[0-46-9]|
            7[0-689]|
            8[0-79]|
            9[0-8]
          )\\d{4}
        ',
    'PossibleNumberPattern' => '\\d{5,7}',
    'ExampleNumber' => '7421234',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => '1[38]\\d{3}',
    'PossibleNumberPattern' => '\\d{5}',
    'ExampleNumber' => '18123',
  ),
  'premiumRate' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'sharedCost' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'personalNumber' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'voip' => 
  array (
    'NationalNumberPattern' => '5[12]\\d{3}',
    'PossibleNumberPattern' => '\\d{5}',
    'ExampleNumber' => '51123',
  ),
  'pager' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
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
  'id' => 'SB',
  'countryCode' => 677,
  'internationalPrefix' => '0[01]',
  'sameMobileAndFixedLinePattern' => false,
  'numberFormat' => 
  array (
    0 => 
    array (
      'pattern' => '(\\d{2})(\\d{5})',
      'format' => '$1 $2',
      'leadingDigitsPatterns' => 
      array (
        0 => '[7-9]',
      ),
      'nationalPrefixFormattingRule' => '',
      'domesticCarrierCodeFormattingRule' => '',
    ),
  ),
  'intlNumberFormat' => 
  array (
  ),
  'mainCountryForCode' => false,
  'leadingZeroPossible' => false,
  'mobileNumberPortableRegion' => false,
);
