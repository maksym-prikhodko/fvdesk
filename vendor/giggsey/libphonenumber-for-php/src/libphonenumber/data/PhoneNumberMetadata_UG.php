<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '\\d{9}',
    'PossibleNumberPattern' => '\\d{5,9}',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '
          20(?:
            [0147]\\d{2}|
            2(?:
              40|
              [5-9]\\d
            )|
            3[23]\\d|
            5[0-4]\\d|
            6[03]\\d|
            8[0-2]\\d
          )\\d{4}|
          [34]\\d{8}
        ',
    'PossibleNumberPattern' => '\\d{5,9}',
    'ExampleNumber' => '312345678',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '
          2030\\d{5}|
          7(?:
            0[0-7]|
            [15789]\\d|
            2[03]|
            30|
            [46][0-4]
          )\\d{6}
        ',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '712345678',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => '800[123]\\d{5}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '800123456',
  ),
  'premiumRate' => 
  array (
    'NationalNumberPattern' => '90[123]\\d{6}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '901123456',
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
  'id' => 'UG',
  'countryCode' => 256,
  'internationalPrefix' => '00[057]',
  'nationalPrefix' => '0',
  'nationalPrefixForParsing' => '0',
  'sameMobileAndFixedLinePattern' => false,
  'numberFormat' => 
  array (
    0 => 
    array (
      'pattern' => '(\\d{3})(\\d{6})',
      'format' => '$1 $2',
      'leadingDigitsPatterns' => 
      array (
        0 => '
            [7-9]|
            20(?:
              [013-8]|
              2[5-9]
            )|
            4(?:
              6[45]|
              [7-9]
            )
          ',
      ),
      'nationalPrefixFormattingRule' => '0$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    1 => 
    array (
      'pattern' => '(\\d{2})(\\d{7})',
      'format' => '$1 $2',
      'leadingDigitsPatterns' => 
      array (
        0 => '
            3|
            4(?:
              [1-5]|
              6[0-36-9]
            )
          ',
      ),
      'nationalPrefixFormattingRule' => '0$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    2 => 
    array (
      'pattern' => '(2024)(\\d{5})',
      'format' => '$1 $2',
      'leadingDigitsPatterns' => 
      array (
        0 => '2024',
      ),
      'nationalPrefixFormattingRule' => '0$1',
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
