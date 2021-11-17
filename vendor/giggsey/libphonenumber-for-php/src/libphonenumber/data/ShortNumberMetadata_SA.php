<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '[19]\\d{2,5}',
    'PossibleNumberPattern' => '\\d{3,6}',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '[19]\\d{2,5}',
    'PossibleNumberPattern' => '\\d{3,6}',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '[19]\\d{2,5}',
    'PossibleNumberPattern' => '\\d{3,6}',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => '
          116111|
          937|
          998
        ',
    'PossibleNumberPattern' => '\\d{3,6}',
    'ExampleNumber' => '116111',
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
          112|
          9(?:
            11|
            9[79]
          )
        ',
    'PossibleNumberPattern' => '\\d{3}',
    'ExampleNumber' => '999',
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
            1(?:
              00|
              2|
              6111
            )|
            410|
            9(?:
              00|
              1[89]|
              9(?:
                099|
                22|
                91
              )
            )
          )|
          9(?:
            0[24-79]|
            11|
            3[379]|
            40|
            66|
            8[5-9]|
            9[02-9]
          )
        ',
    'PossibleNumberPattern' => '\\d{3,6}',
    'ExampleNumber' => '937',
  ),
  'standardRate' => 
  array (
    'NationalNumberPattern' => '1410',
    'PossibleNumberPattern' => '\\d{4}',
    'ExampleNumber' => '1410',
  ),
  'carrierSpecific' => 
  array (
    'NationalNumberPattern' => '
          1(?:
            100|
            410
          )|
          90[24679]
        ',
    'PossibleNumberPattern' => '\\d{3,4}',
    'ExampleNumber' => '902',
  ),
  'noInternationalDialling' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'id' => 'SA',
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
