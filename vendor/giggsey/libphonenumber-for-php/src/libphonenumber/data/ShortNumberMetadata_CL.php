<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '[1-9]\\d{2,4}',
    'PossibleNumberPattern' => '\\d{3,5}',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '[1-9]\\d{2,4}',
    'PossibleNumberPattern' => '\\d{3,5}',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '[1-9]\\d{2,4}',
    'PossibleNumberPattern' => '\\d{3,5}',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => '
          1213|
          4342
        ',
    'PossibleNumberPattern' => '\\d{4}',
    'ExampleNumber' => '4342',
  ),
  'premiumRate' => 
  array (
    'NationalNumberPattern' => '
          1(?:
            060|
            211|
            3(?:
              13|
              [348]0|
              5[01]
            )|
            417|
            560|
            818|
            9(?:
              19|
              80
            )
          )|
          2(?:
            0122|
            22[47]|
            323|
            777|
            882
          )|
          3(?:
            0(?:
              51|
              99
            )|
            132|
            3(?:
              29|
              77|
              90
            )|
            665
          )|
          4(?:
            142|
            243|
            3656|
            4(?:
              02|
              15|
              77
            )|
            554
          )|
          5(?:
            004|
            4154|
            5(?:
              66|
              77
            )|
            995
          )|
          6(?:
            0700|
            131|
            222|
            3(?:
              00|
              66
            )|
            500|
            699
          )|
          7878|
          8(?:
            011|
            11[28]|
            482|
            889
          )|
          9(?:
            011|
            [12]00|
            330
          )
        ',
    'PossibleNumberPattern' => '\\d{3,5}',
    'ExampleNumber' => '2224',
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
           13[123]|
           911
        ',
    'PossibleNumberPattern' => '\\d{3,5}',
    'ExampleNumber' => '133',
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
            06?0|
            21[13]|
            3(?:
              [02679]|
              13?|
              [348]0?|
              5[01]?
            )|
            4(?:
              0[02-6]|
              17|
              [379]
            )|
            560|
            818|
            9(?:
              19|
              80
            )
          )|
          2(?:
            0(?:
              01|
              122
            )|
            22[47]|
            323|
            777|
            882
          )|
          3(?:
            0(?:
              51|
              99
            )|
            132|
            3(?:
              29|
              37|
              77|
              90
            )|
            665
          )|
          4(?:
            142|
            243|
            3(?:
              42|
              656
            )|
            4(?:
              02|
              15|
              77
            )|
            554
          )|
          5(?:
            004|
            4154|
            5(?:
              66|
              77
            )|
            995
          )|
          6(?:
            0700|
            131|
            222|
            3(?:
              00|
              66
            )|
            500|
            699
          )|
          7878|
          8(?:
            011|
            11[28]|
            482|
            889
          )|
          9(?:
            011|
            1(?:
             1|
             00
            )|
            200|
            330
          )
        ',
    'PossibleNumberPattern' => '\\d{3,5}',
    'ExampleNumber' => '139',
  ),
  'standardRate' => 
  array (
    'NationalNumberPattern' => '
          2001|
          3337
        ',
    'PossibleNumberPattern' => '\\d{4}',
    'ExampleNumber' => '3337',
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
  'id' => 'CL',
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
