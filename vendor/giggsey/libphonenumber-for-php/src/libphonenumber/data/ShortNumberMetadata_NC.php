<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '
          1\\d{1,3}|
          3\\d{3}|
          5\\d{2}
        ',
    'PossibleNumberPattern' => '\\d{2,4}',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '
          1\\d{1,3}|
          3\\d{3}|
          5\\d{2}
        ',
    'PossibleNumberPattern' => '\\d{2,4}',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '
          1\\d{1,3}|
          3\\d{3}|
          5\\d{2}
        ',
    'PossibleNumberPattern' => '\\d{2,4}',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => '
          10(?:
            00|
            1[23]|
            3[0-2]|
            88
          )|
          3631|
          577
        ',
    'PossibleNumberPattern' => '\\d{3,4}',
    'ExampleNumber' => '1000',
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
    'NationalNumberPattern' => '1[5-8]',
    'PossibleNumberPattern' => '\\d{2}',
    'ExampleNumber' => '15',
  ),
  'voicemail' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'shortCode' => 
  array (
    'NationalNumberPattern' => '
          1(?:
            0(?:
              0[06]|
              1[02-46]|
              20|
              3[0125]|
              42|
              5[058]|
              77|
              88
            )|
            [5-8]
          )|
          3631|
          5[6-8]\\d
        ',
    'PossibleNumberPattern' => '\\d{2,4}',
    'ExampleNumber' => '1000',
  ),
  'standardRate' => 
  array (
    'NationalNumberPattern' => '
          5(?:
            67|
            88
          )
        ',
    'PossibleNumberPattern' => '\\d{3}',
    'ExampleNumber' => '567',
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
  'id' => 'NC',
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
