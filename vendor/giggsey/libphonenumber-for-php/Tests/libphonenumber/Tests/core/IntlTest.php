<?php
namespace libphonenumber\Tests\core;
use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
use libphonenumber\PhoneNumberToCarrierMapper;
class IntlTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (extension_loaded('intl')) {
            $this->markTestSkipped('The intl extension must not be installed');
        }
    }
    public function testPhoneNumberOfflineGeocoder()
    {
        PhoneNumberOfflineGeocoder::getInstance();
    }
    public function testPhoneNumberToCarrierMapper()
    {
        PhoneNumberToCarrierMapper::getInstance();
    }
}
