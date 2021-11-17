<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '[2-9]\\d{7,8}',
    'PossibleNumberPattern' => '\\d{6,9}',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '
          (?:
            20[2-8]|
            3(?:
              0[2-7]|
              [12][35-7]|
              3[4-7]
            )|
            4(?:
              0[2367]|
              1[267]
            )|
            5(?:
              0[467]|
              1[267]|
              2[367]
            )
          )\\d{5}
        ',
    'PossibleNumberPattern' => '\\d{6,8}',
    'ExampleNumber' => '30234567',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '
          6(?:
            00\\d|
            32\\d|
            [89]\\d{2}|
            61\\d|
            7(?:
              [0-8]\\d|
              9(?:
                [3-9]|
                [0-2]\\d
              )
            )
          )\\d{4}
        ',
    'PossibleNumberPattern' => '\\d{8,9}',
    'ExampleNumber' => '67622901',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => '80\\d{6}',
    'PossibleNumberPattern' => '\\d{8}',
    'ExampleNumber' => '80080002',
  ),
  'premiumRate' => 
  array (
    'NationalNumberPattern' => '
          (?:
            9(?:
              4[1568]|
              5[178]
            )
          )\\d{5}
        ',
    'PossibleNumberPattern' => '\\d{8}',
    'ExampleNumber' => '94515151',
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
    'NationalNumberPattern' => '78[1-9]\\d{5}',
    'PossibleNumberPattern' => '\\d{8}',
    'ExampleNumber' => '78108780',
  ),
  'pager' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
  ),
  'uan' => 
  array (
    'NationalNumberPattern' => '77\\d{6}',
    'PossibleNumberPattern' => '\\d{8}',
    'ExampleNumber' => '77273012',
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
  'id' => 'ME',
  'countryCode' => 382,
  'internationalPrefix' => '00',
  'nationalPrefix' => '0',
  'nationalPrefixForParsing' => '0',
  'sameMobileAndFixedLinePattern' => false,
  'numberFormat' => 
  array (
    0 => 
    array (
      'pattern' => '(\\d{2})(\\d{3})(\\d{3})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '[2-57-9]|6[036-9]',
        1 => '
            [2-57-9]|
            6(?:
              [03689]|
              7(?:
                [0-8]|
                9[3-9]
              )
            )
          ',
      ),
      'nationalPrefixFormattingRule' => '0$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    1 => 
    array (
      'pattern' => '(67)(9)(\\d{3})(\\d{3})',
      'format' => '$1 $2 $3 $4',
      'leadingDigitsPatterns' => 
      array (
        0 => '679',
        1 => '679[0-2]',
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
