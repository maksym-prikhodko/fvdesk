<?php
return array (
  'generalDesc' => 
  array (
    'NationalNumberPattern' => '
          [2-79]\\d{7,8}|
          800\\d{2,9}
        ',
    'PossibleNumberPattern' => '\\d{5,12}',
  ),
  'fixedLine' => 
  array (
    'NationalNumberPattern' => '[2-4679][2-8]\\d{6}',
    'PossibleNumberPattern' => '\\d{7,8}',
    'ExampleNumber' => '22345678',
  ),
  'mobile' => 
  array (
    'NationalNumberPattern' => '5[0256]\\d{7}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '501234567',
  ),
  'tollFree' => 
  array (
    'NationalNumberPattern' => '
          400\\d{6}|
          800\\d{2,9}
        ',
    'PossibleNumberPattern' => '\\d{5,12}',
    'ExampleNumber' => '800123456',
  ),
  'premiumRate' => 
  array (
    'NationalNumberPattern' => '900[02]\\d{5}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '900234567',
  ),
  'sharedCost' => 
  array (
    'NationalNumberPattern' => '700[05]\\d{5}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '700012345',
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
    'NationalNumberPattern' => '600[25]\\d{5}',
    'PossibleNumberPattern' => '\\d{9}',
    'ExampleNumber' => '600212345',
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
  'id' => 'AE',
  'countryCode' => 971,
  'internationalPrefix' => '00',
  'nationalPrefix' => '0',
  'nationalPrefixForParsing' => '0',
  'sameMobileAndFixedLinePattern' => false,
  'numberFormat' => 
  array (
    0 => 
    array (
      'pattern' => '([2-4679])(\\d{3})(\\d{4})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '[2-4679][2-8]',
      ),
      'nationalPrefixFormattingRule' => '0$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    1 => 
    array (
      'pattern' => '(5[0256])(\\d{3})(\\d{4})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '5',
      ),
      'nationalPrefixFormattingRule' => '0$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    2 => 
    array (
      'pattern' => '([479]00)(\\d)(\\d{5})',
      'format' => '$1 $2 $3',
      'leadingDigitsPatterns' => 
      array (
        0 => '[479]0',
      ),
      'nationalPrefixFormattingRule' => '$1',
      'domesticCarrierCodeFormattingRule' => '',
    ),
    3 => 
    array (
      'pattern' => '([68]00)(\\d{2,9})',
      'format' => '$1 $2',
      'leadingDigitsPatterns' => 
      array (
        0 => '
            60|
            8
          ',
      ),
      'nationalPrefixFormattingRule' => '$1',
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
