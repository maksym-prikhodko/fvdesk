<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '[2-57-9]\\d{5}',
    'PossibleNumberPattern' => '\\d{6}',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '
          (?:
            2[03-9]|
            3[0-5]|
            4[1-7]|
            88
          )\\d{4}
        ',
    'PossibleNumberPattern' => '\\d{6}',
    'ExampleNumber' => '201234',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '
          (?:
            5[0-4]|
            [79]\\d|
            8[0-79]
          )\\d{4}
        ',
    'PossibleNumberPattern' => '\\d{6}',
    'ExampleNumber' => '751234',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'premiumRate' => 
  array (
    'NationalNumberPattern' => '36\\d{4}',
    'PossibleNumberPattern' => '\\d{6}',
    'ExampleNumber' => '366711',
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
  'id' => 'NC',
  'countryCode' => 687,
  'internationalPrefix' => '00',
  'sameMobileAndFixedLinePattern' => false,
  'numberFormat' => 
  array (
    0 => 
    array (
      'pattern' => '(\\d{2})(\\d{2})(\\d{2})',
      'format' => '$1.$2.$3',
      'leadingDigitsPatterns' => 
      array (
        0 => '
            [2-46-9]|
            5[0-4]
          ',
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
