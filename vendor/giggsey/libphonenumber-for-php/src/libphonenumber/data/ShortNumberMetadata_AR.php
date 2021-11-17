<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '[0189]\\d{1,4}',
    'PossibleNumberPattern' => '\\d{2,5}',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '[0189]\\d{1,4}',
    'PossibleNumberPattern' => '\\d{2,5}',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '[0189]\\d{1,4}',
    'PossibleNumberPattern' => '\\d{2,5}',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => '
          [09]\\d{2}|
          1(?:
            [02-9]\\d?|
            1[0-24-9]?
          )
        ',
    'PossibleNumberPattern' => '\\d{2,3}',
    'ExampleNumber' => '111',
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
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
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
    'NationalNumberPattern' => '
          10[017]|
          911
        ',
    'PossibleNumberPattern' => '\\d{3}',
    'ExampleNumber' => '101',
  ),
  'voicemail' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'shortCode' => 
  array (
    'NationalNumberPattern' => '
          000|
          1(?:
            0[0-35-7]|
            1[02-5]|
            2[15]|
            9
          )|
          89338|
          911
        ',
    'PossibleNumberPattern' => '\\d{2,5}',
    'ExampleNumber' => '121',
  ),
  'standardRate' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'carrierSpecific' => 
  array (
    'NationalNumberPattern' => '89338',
    'PossibleNumberPattern' => '\\d{5}',
  ),
  'noInternationalDialling' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'id' => 'AR',
  'countryCode' => 0,
  'internationalPrefix' => '',
  'sameMobileAndFixedLinePattern' => true,
  'numberFormat' => 
  array (
  ),
  'intlNumberFormat' => 
  array (
  ),
  'mainCountryForCode' => false,
  'leadingZeroPossible' => false,
  'mobileNumberPortableRegion' => false,
);
