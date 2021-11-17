<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '[1-9]\\d{8}',
    'PossibleNumberPattern' => '\\d{7,9}',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '
          (?:
            1[28]\\d|
            2(?:
              02|
              1[24]|
              2[2-4]|
              33|
              [45]2|
              6[23]
            )|
            365
          )\\d{6}
        ',
    'PossibleNumberPattern' => '\\d{7,9}',
    'ExampleNumber' => '123123456',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '
          (?:
            4[04]|
            5[015]|
            60|
            7[07]
          )\\d{7}
        ',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '401234567',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => '88\\d{7}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '881234567',
  ),
  'premiumRate' => 
  array (
    'NationalNumberPattern' => '900200\\d{3}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '900200123',
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
  'id' => 'AZ',
  'countryCode' => 994,
  'internationalPrefix' => '00',
  'nationalPrefix' => '0',
  'nationalPrefixForParsing' => '0',
  'sameMobileAndFixedLinePattern' => false,
  'numberFormat' => 
  array (
    0 => 
    array (
      'pattern' => '(\\d{2})(\\d{3})(\\d{2})(\\d{2})',
      'format' => '$1 $2 $3 $4',
      'leadingDigitsPatterns' => 
      array (
        0 => '
            (?:
              1[28]|
              2(?:
                [45]2|
                [0-36]
              )|
              365
            )
          ',
      ),
      'nationalPrefixFormattingRule' => '(0$1)',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    1 => 
    array (
      'pattern' => '(\\d{2})(\\d{3})(\\d{2})(\\d{2})',
      'format' => '$1 $2 $3 $4',
      'leadingDigitsPatterns' => 
      array (
        0 => '[4-8]',
      ),
      'nationalPrefixFormattingRule' => '0$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    2 => 
    array (
      'pattern' => '(\\d{3})(\\d{2})(\\d{2})(\\d{2})',
      'format' => '$1 $2 $3 $4',
      'leadingDigitsPatterns' => 
      array (
        0 => '9',
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
