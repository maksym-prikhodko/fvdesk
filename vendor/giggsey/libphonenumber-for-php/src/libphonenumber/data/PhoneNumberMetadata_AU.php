<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '[1-578]\\d{5,9}',
    'PossibleNumberPattern' => '\\d{6,10}',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '
          [237]\\d{8}|
          8(?:
            [68]\\d{3}|
            7[0-69]\\d{2}|
            9(?:
              [02-9]\\d{2}|
              1(?:
                [0-57-9]\\d|
                6[0135-9]
              )
            )
          )\\d{4}
        ',
    'PossibleNumberPattern' => '\\d{8,9}',
    'ExampleNumber' => '212345678',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '
          14(?:
            5\\d|
            71
          )\\d{5}|
          4(?:
            [0-2]\\d|
            3[0-57-9]|
            4[47-9]|
            5[0-25-9]|
            6[6-9]|
            7[03-9]|
            8[17-9]|
            9[017-9]
          )\\d{6}
        ',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '412345678',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => '
          180(?:
            0\\d{3}|
            2
          )\\d{3}
        ',
    'PossibleNumberPattern' => '\\d{7,10}',
    'ExampleNumber' => '1800123456',
  ),
  'premiumRate' => 
  array (
    'NationalNumberPattern' => '190[0126]\\d{6}',
    'PossibleNumberPattern' => '\\d{10}',
    'ExampleNumber' => '1900123456',
  ),
  'sharedCost' => 
  array (
    'NationalNumberPattern' => '
          13(?:
            00\\d{2}
          )?\\d{4}
        ',
    'PossibleNumberPattern' => '\\d{6,10}',
    'ExampleNumber' => '1300123456',
  ),
  'personalNumber' => 
  array (
    'NationalNumberPattern' => '500\\d{6}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '500123456',
  ),
  'voip' => 
  array (
    'NationalNumberPattern' => '550\\d{6}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '550123456',
  ),
  'pager' => 
  array (
    'NationalNumberPattern' => '16\\d{3,7}',
    'PossibleNumberPattern' => '\\d{5,9}',
    'ExampleNumber' => '1612345',
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
    'NationalNumberPattern' => '
          1(?:
            3(?:
              \\d{4}|
              00\\d{6}
            )|
            80(?:
              0\\d{6}|
              2\\d{3}
            )
          )
        ',
    'PossibleNumberPattern' => '\\d{6,10}',
    'ExampleNumber' => '1300123456',
  ),
  'id' => 'AU',
  'countryCode' => 61,
  'internationalPrefix' => '(?:14(?:1[14]|34|4[17]|[56]6|7[47]|88))?001[14-689]',
  'preferredInternationalPrefix' => '0011',
  'nationalPrefix' => '0',
  'nationalPrefixForParsing' => '0',
  'sameMobileAndFixedLinePattern' => false,
  'numberFormat' => 
  array (
    0 => 
    array (
      'pattern' => '([2378])(\\d{4})(\\d{4})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '[2378]',
      ),
      'nationalPrefixFormattingRule' => '(0$1)',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    1 => 
    array (
      'pattern' => '(\\d{3})(\\d{3})(\\d{3})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '
            [45]|
            14
          ',
      ),
      'nationalPrefixFormattingRule' => '0$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    2 => 
    array (
      'pattern' => '(16)(\\d{3})(\\d{2,4})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '16',
      ),
      'nationalPrefixFormattingRule' => '0$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    3 => 
    array (
      'pattern' => '(1[389]\\d{2})(\\d{3})(\\d{3})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '
            1(?:
              [38]0|
              90
            )
          ',
        1 => '
            1(?:
              [38]00|
              90
            )
          ',
      ),
      'nationalPrefixFormattingRule' => '$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    4 => 
    array (
      'pattern' => '(180)(2\\d{3})',
      'format' => '$1 $2',
      'leadingDigitsPatterns' => 
      array (
        0 => '180',
        1 => '1802',
      ),
      'nationalPrefixFormattingRule' => '$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    5 => 
    array (
      'pattern' => '(19\\d)(\\d{3})',
      'format' => '$1 $2',
      'leadingDigitsPatterns' => 
      array (
        0 => '19[13]',
      ),
      'nationalPrefixFormattingRule' => '$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    6 => 
    array (
      'pattern' => '(19\\d{2})(\\d{4})',
      'format' => '$1 $2',
      'leadingDigitsPatterns' => 
      array (
        0 => '19[67]',
      ),
      'nationalPrefixFormattingRule' => '$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    7 => 
    array (
      'pattern' => '(13)(\\d{2})(\\d{2})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '13[1-9]',
      ),
      'nationalPrefixFormattingRule' => '$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
  ),
  'intlNumberFormat' => 
  array (
  ),
  'mainCountryForCode' => true,
  'leadingZeroPossible' => false,
  'mobileNumberPortableRegion' => true,
);
