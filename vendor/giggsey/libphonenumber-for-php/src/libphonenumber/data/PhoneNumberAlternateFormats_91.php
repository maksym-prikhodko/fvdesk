<?php
return array (
  'generalDesc' => 
  array (
  ),
  'fixedLine' => 
  array (
  ),
  'mobile' => 
  array (
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => 'NA',
    'PossibleNumberPattern' => 'NA',
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
  'id' => '',
  'countryCode' => 91,
  'internationalPrefix' => '',
  'sameMobileAndFixedLinePattern' => true,
  'numberFormat' => 
  array (
    0 => 
    array (
      'pattern' => '(\\d{2})(\\d{2})(\\d{6})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '
             7(?:
               0[2-8]|
               2[0579]|
               3[057-9]|
               4[0-389]|
               6[0-35-9]|
               [57]|
               8[0-79]
             )|
             8(?:
               0[015689]|
               1[0-57-9]|
               2[2356-9]|
               3[0-57-9]|
               [45]|
               6[02457-9]|
               7[1-69]|
               8[0124-9]|
               9[02-9]
             )|
             9
           ',
        1 => '
             7(?:
               0(?:
                 2[2-9]|
                 [3-7]|
                 8[0-7]
               )|
               2(?:
                 0[04-9]|
                 5[09]|
                 7[5-8]|
                 9[389]
               )|
               3(?:
                 0[1-9]|
                 [58]|
                 7[3679]|
                 9[689]
               )|
               4(?:
                 0[1-9]|
                 1[15-9]|
                 [29][89]|
                 39|
                 8[389]
               )|
               5(?:
                 [034678]|
                 2[03-9]|
                 5[017-9]|
                 9[7-9]
               )|
               6(?:
                 0[0-27]|
                 1[0-257-9]|
                 2[0-4]|
                 3[19]|
                 5[4589]|
                 [6-9]
               )|
               7(?:
                 0[2-9]|
                 [1-79]|
                 8[1-9]
               )|
               8(?:
                 [0-7]|
                 9[013-9]
               )
             )|
             8(?:
               0(?:
                 [01589]|
                 6[67]
               )|
               1(?:
                 [02-589]|
                 1[0135-9]|
                 7[0-79]
               )|
               2(?:
                 [236-9]|
                 5[1-9]
               )|
               3(?:
                 [0357-9]|
                 4[1-9]
               )|
               [45]|
               6[02457-9]|
               7[1-69]|
               8(?:
                 [0-26-9]|
                 44|
                 5[2-9]
               )|
               9(?:
                 [035-9]|
                 2[2-9]|
                 4[0-8]
               )
             )|
             9
           ',
      ),
      'nationalPrefixFormattingRule' => '($1)',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    1 => 
    array (
      'pattern' => '(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})',
      'format' => '$1 $2 $3 $4 $5',
      'leadingDigitsPatterns' => 
      array (
        0 => '
             7(?:
               0[2-8]|
               2[0579]|
               3[057-9]|
               4[0-389]|
               6[0-35-9]|
               [57]|
               8[0-79]
             )|
             8(?:
               0[015689]|
               1[0-57-9]|
               2[2356-9]|
               3[0-57-9]|
               [45]|
               6[02457-9]|
               7[1-69]|
               8[0124-9]|
               9[02-9]
             )|
             9
           ',
        1 => '
             7(?:
               0(?:
                 2[2-9]|
                 [3-7]|
                 8[0-7]
               )|
               2(?:
                 0[04-9]|
                 5[09]|
                 7[5-8]|
                 9[389]
               )|
               3(?:
                 0[1-9]|
                 [58]|
                 7[3679]|
                 9[689]
               )|
               4(?:
                 0[1-9]|
                 1[15-9]|
                 [29][89]|
                 39|
                 8[389]
               )|
               5(?:
                 [034678]|
                 2[03-9]|
                 5[017-9]|
                 9[7-9]
               )|
               6(?:
                 0[0-27]|
                 1[0-257-9]|
                 2[0-4]|
                 3[19]|
                 5[4589]|
                 [6-9]
               )|
               7(?:
                 0[2-9]|
                 [1-79]|
                 8[1-9]
               )|
               8(?:
                 [0-7]|
                 9[013-9]
               )
             )|
             8(?:
               0(?:
                 [01589]|
                 6[67]
               )|
               1(?:
                 [02-589]|
                 1[0135-9]|
                 7[0-79]
               )|
               2(?:
                 [236-9]|
                 5[1-9]
               )|
               3(?:
                 [0357-9]|
                 4[1-9]
               )|
               [45]|
               6[02457-9]|
               7[1-69]|
               8(?:
                 [0-26-9]|
                 44|
                 5[2-9]
               )|
               9(?:
                 [035-9]|
                 2[2-9]|
                 4[0-8]
               )
             )|
             9
           ',
      ),
      'nationalPrefixFormattingRule' => '($1)',
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
