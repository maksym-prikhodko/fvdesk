<?php
namespace libphonenumber\geocoding;
class Locale extends \Locale
{
    public static function countryCodeToLocale($country_code, $language_code = '')
    {
        $locale = 'en-' . $country_code;
        $locale_region = locale_get_region($locale);
        $locale_language = locale_get_primary_language($locale);
        $locale_array = array(
            'language' => $locale_language,
            'region' => $locale_region
        );
        if (strtoupper($country_code) == $locale_region && $language_code == '') {
            return locale_compose($locale_array);
        } elseif (strtoupper($country_code) == $locale_region && strtolower($language_code) == $locale_language) {
            return locale_compose($locale_array);
        }
        return null;
    }
}
