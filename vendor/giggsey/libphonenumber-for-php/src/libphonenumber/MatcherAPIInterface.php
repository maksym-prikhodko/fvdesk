<?php
namespace libphonenumber;
interface MatcherAPIInterface
{
    public function matchesNationalNumber($nationalNumber, PhoneNumberDesc $numberDesc, $allowPrefixMatch);
    public function matchesPossibleNumber($nationalNumber, PhoneNumberDesc $numberDesc);
}
