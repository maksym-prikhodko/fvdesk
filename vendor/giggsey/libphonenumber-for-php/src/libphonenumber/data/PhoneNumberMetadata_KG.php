<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '[235-8]\\d{8,9}',
    'PossibleNumberPattern' => '\\d{5,10}',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '
          (?:
            3(?:
              1(?:
                [256]\\d|
                3[1-9]|
                47
              )|
              2(?:
                22|
                3[0-479]|
                6[0-7]
              )|
              4(?:
                22|
                5[6-9]|
                6\\d
              )|
              5(?:
                22|
                3[4-7]|
                59|
                6\\d
              )|
              6(?:
                22|
                5[35-7]|
                6\\d
              )|
              7(?:
                22|
                3[468]|
                4[1-9]|
                59|
                [67]\\d
              )|
              9(?:
                22|
                4[1-8]|
                6\\d
              )
            )|
            6(?:
              09|
              12|
              2[2-4]
            )\\d
          )\\d{5}
        ',
    'PossibleNumberPattern' => '\\d{5,10}',
    'ExampleNumber' => '312123456',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '
          (?:
            20[0-35]|
            5[124-7]\\d|
            7[07]\\d
          )\\d{6}
        ',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '700123456',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => '800\\d{6,7}',
    'PossibleNumberPattern' => '\\d{9,10}',
    'ExampleNumber' => '800123456',
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
  'id' => 'KG',
  'countryCode' => 996,
  'internationalPrefix' => '00',
  'nationalPrefix' => '0',
  'nationalPrefixForParsing' => '0',
  'sameMobileAndFixedLinePattern' => false,
  'numberFormat' => 
  array (
    0 => 
    array (
      'pattern' => '(\\d{3})(\\d{3})(\\d{3})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '
            [25-7]|
            31[25]
          ',
      ),
      'nationalPrefixFormattingRule' => '0$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    1 => 
    array (
      'pattern' => '(\\d{4})(\\d{5})',
      'format' => '$1 $2',
      'leadingDigitsPatterns' => 
      array (
        0 => '
            3(?:
              1[36]|
              [2-9]
            )
          ',
      ),
      'nationalPrefixFormattingRule' => '0$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    2 => 
    array (
      'pattern' => '(\\d{3})(\\d{3})(\\d)(\\d{3})',
      'format' => '$1 $2 $3 $4',
      'leadingDigitsPatterns' => 
      array (
        0 => '8',
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