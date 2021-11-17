<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '
          0\\d{4}|
          [4789]\\d{7}
        ',
    'PossibleNumberPattern' => '\\d{5}(?:\\d{3})?',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '79\\d{6}',
    'PossibleNumberPattern' => '\\d{8}',
    'ExampleNumber' => '79123456',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '
          (?:
            4[015-8]|
            5[89]|
            9\\d
          )\\d{6}
        ',
    'PossibleNumberPattern' => '\\d{8}',
    'ExampleNumber' => '41234567',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => '80[01]\\d{5}',
    'PossibleNumberPattern' => '\\d{8}',
    'ExampleNumber' => '80012345',
  ),
  'premiumRate' => 
  array (
    'NationalNumberPattern' => '82[09]\\d{5}',
    'PossibleNumberPattern' => '\\d{8}',
    'ExampleNumber' => '82012345',
  ),
  'sharedCost' => 
  array (
    'NationalNumberPattern' => '
          810(?:
            0[0-6]|
            [2-8]\\d
          )\\d{3}
        ',
    'PossibleNumberPattern' => '\\d{8}',
    'ExampleNumber' => '81021234',
  ),
  'personalNumber' => 
  array (
    'NationalNumberPattern' => '880\\d{5}',
    'PossibleNumberPattern' => '\\d{8}',
    'ExampleNumber' => '88012345',
  ),
  'voip' => 
  array (
    'NationalNumberPattern' => '85[0-5]\\d{5}',
    'PossibleNumberPattern' => '\\d{8}',
    'ExampleNumber' => '85012345',
  ),
  'pager' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'uan' => 
  array (
    'NationalNumberPattern' => '
          0\\d{4}|
          81(?:
            0(?:
              0[7-9]|
              1\\d
            )|
            5\\d{2}
          )\\d{3}
        ',
    'PossibleNumberPattern' => '\\d{5}(?:\\d{3})?',
    'ExampleNumber' => '01234',
  ),
  'emergency' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'voicemail' => 
  array (
    'NationalNumberPattern' => '81[23]\\d{5}',
    'PossibleNumberPattern' => '\\d{8}',
    'ExampleNumber' => '81212345',
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
  'id' => 'SJ',
  'countryCode' => 47,
  'internationalPrefix' => '00',
  'sameMobileAndFixedLinePattern' => false,
  'numberFormat' => 
  array (
  ),
  'intlNumberFormat' => 
  array (
  ),
  'mainCountryForCode' => false,
  'leadingZeroPossible' => true,
  'mobileNumberPortableRegion' => false,
);
