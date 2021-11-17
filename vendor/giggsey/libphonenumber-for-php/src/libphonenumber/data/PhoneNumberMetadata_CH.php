<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '
          [2-9]\\d{8}|
          860\\d{9}
        ',
    'PossibleNumberPattern' => '\\d{9}(?:\\d{3})?',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '
          (?:
            2[12467]|
            3[1-4]|
            4[134]|
            5[256]|
            6[12]|
            [7-9]1
          )\\d{7}
        ',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '212345678',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '7[5-9]\\d{7}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '781234567',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => '800\\d{6}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '800123456',
  ),
  'premiumRate' => 
  array (
    'NationalNumberPattern' => '90[016]\\d{6}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '900123456',
  ),
  'sharedCost' => 
  array (
    'NationalNumberPattern' => '84[0248]\\d{6}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '840123456',
  ),
  'personalNumber' => 
  array (
    'NationalNumberPattern' => '878\\d{6}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '878123456',
  ),
  'voip' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'pager' => 
  array (
    'NationalNumberPattern' => '74[0248]\\d{6}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '740123456',
  ),
  'uan' => 
  array (
    'NationalNumberPattern' => '5[18]\\d{7}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '581234567',
  ),
  'emergency' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'voicemail' => 
  array (
    'NationalNumberPattern' => '860\\d{9}',
    'PossibleNumberPattern' => '\\d{12}',
    'ExampleNumber' => '860123456789',
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
  'id' => 'CH',
  'countryCode' => 41,
  'internationalPrefix' => '00',
  'nationalPrefix' => '0',
  'nationalPrefixForParsing' => '0',
  'sameMobileAndFixedLinePattern' => false,
  'numberFormat' => 
  array (
    0 => 
    array (
      'pattern' => '([2-9]\\d)(\\d{3})(\\d{2})(\\d{2})',
      'format' => '$1 $2 $3 $4',
      'leadingDigitsPatterns' => 
      array (
        0 => '
            [2-7]|
            [89]1
          ',
      ),
      'nationalPrefixFormattingRule' => '0$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    1 => 
    array (
      'pattern' => '([89]\\d{2})(\\d{3})(\\d{3})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '
            8[047]|
            90
          ',
      ),
      'nationalPrefixFormattingRule' => '0$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    2 => 
    array (
      'pattern' => '(\\d{3})(\\d{2})(\\d{3})(\\d{2})(\\d{2})',
      'format' => '$1 $2 $3 $4 $5',
      'leadingDigitsPatterns' => 
      array (
        0 => '860',
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
  'mobileNumberPortableRegion' => true,
);
