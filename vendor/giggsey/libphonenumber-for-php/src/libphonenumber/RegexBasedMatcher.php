<?php
namespace libphonenumber;
class RegexBasedMatcher implements MatcherAPIInterface
{
    public static function create()
    {
        return new static();
    }
    public function matchesNationalNumber($nationalNumber, PhoneNumberDesc $numberDesc, $allowPrefixMatch)
    {
        $nationalNumberPatternMatcher = new Matcher($numberDesc->getNationalNumberPattern(), $nationalNumber);
        return ($nationalNumberPatternMatcher->matches()
            || ($allowPrefixMatch && $nationalNumberPatternMatcher->lookingAt()));
    }
    public function matchesPossibleNumber($nationalNumber, PhoneNumberDesc $numberDesc)
    {
        $possibleNumberPatternMatcher = new Matcher($numberDesc->getPossibleNumberPattern(), $nationalNumber);
        return $possibleNumberPatternMatcher->matches();
    }
}
