<?php
namespace libphonenumber\Tests\geocoding;
use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
use libphonenumber\PhoneNumber;
class PhoneNumberOfflineGeocoderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_META_DATA_FILE_PREFIX = "/../../../Tests/libphonenumber/Tests/prefixmapper/data/";
    private static $KO_Number1;
    private static $KO_Number2;
    private static $KO_Number3;
    private static $KO_InvalidNumber;
    private static $US_Number1;
    private static $US_Number2;
    private static $US_Number3;
    private static $US_Number4;
    private static $US_InvalidNumber;
    private static $NANPA_TollFree;
    private static $BS_Number1;
    private static $AU_Number;
    private static $AR_MobileNumber;
    private static $numberWithInvalidCountryCode;
    private static $internationalTollFree;
    protected $geocoder;
    public static function setUpBeforeClass()
    {
        self::$KO_Number1 = new PhoneNumber();
        self::$KO_Number1->setCountryCode(82)->setNationalNumber(22123456);
        self::$KO_Number2 = new PhoneNumber();
        self::$KO_Number2->setCountryCode(82)->setNationalNumber(322123456);
        self::$KO_Number3 = new PhoneNumber();
        self::$KO_Number3->setCountryCode(82)->setNationalNumber(6421234567);
        self::$KO_InvalidNumber = new PhoneNumber();
        self::$KO_InvalidNumber->setCountryCode(82)->setNationalNumber(1234);
        self::$US_Number1 = new PhoneNumber();
        self::$US_Number1->setCountryCode(1)->setNationalNumber(6502530000);
        self::$US_Number2 = new PhoneNumber();
        self::$US_Number2->setCountryCode(1)->setNationalNumber(6509600000);
        self::$US_Number3 = new PhoneNumber();
        self::$US_Number3->setCountryCode(1)->setNationalNumber(2128120000);
        self::$US_Number4 = new PhoneNumber();
        self::$US_Number4->setCountryCode(1)->setNationalNumber(6174240000);
        self::$US_InvalidNumber = new PhoneNumber();
        self::$US_InvalidNumber->setCountryCode(1)->setNationalNumber(123456789);
        self::$NANPA_TollFree = new PhoneNumber();
        self::$NANPA_TollFree->setCountryCode(1)->setNationalNumber(8002431234);
        self::$BS_Number1 = new PhoneNumber();
        self::$BS_Number1->setCountryCode(1)->setNationalNumber(2423651234);
        self::$AU_Number = new PhoneNumber();
        self::$AU_Number->setCountryCode(61)->setNationalNumber(236618300);
        self::$AR_MobileNumber = new PhoneNumber();
        self::$AR_MobileNumber->setCountryCode(54)->setNationalNumber(92214000000);
        self::$numberWithInvalidCountryCode = new PhoneNumber();
        self::$numberWithInvalidCountryCode->setCountryCode(999)->setNationalNumber(2423651234);
        self::$internationalTollFree = new PhoneNumber();
        self::$internationalTollFree->setCountryCode(800)->setNationalNumber(12345678);
    }
    public function setUp()
    {
        if(!extension_loaded('intl')) {
            $this->markTestSkipped('The intl extension must be installed');
        }
        PhoneNumberOfflineGeocoder::resetInstance();
        $this->geocoder = PhoneNumberOfflineGeocoder::getInstance(self::TEST_META_DATA_FILE_PREFIX);
    }
    public function testGetDescriptionForNumberWithNoDataFile()
    {
        $this->assertEquals(
            pack('H*', 'e7be8e') . pack('H*', 'e59bbd'),
            $this->geocoder->getDescriptionForNumber(self::$US_Number1, "zh_CN")
        );
        $this->assertEquals("Bahamas", $this->geocoder->getDescriptionForNumber(self::$BS_Number1, "en_US"));
        $this->assertEquals("Australia", $this->geocoder->getDescriptionForNumber(self::$AU_Number, "en_US"));
        $this->assertEquals("", $this->geocoder->getDescriptionForNumber(self::$numberWithInvalidCountryCode, "en_US"));
        $this->assertEquals("", $this->geocoder->getDescriptionForNumber(self::$internationalTollFree, "en_US"));
    }
    public function testGetDescriptionForNumberWithMissingPrefix()
    {
        $this->assertEquals("United States", $this->geocoder->getDescriptionForNumber(self::$US_Number4, "en_US"));
    }
    public function testGetDescriptionForNumberBelongingToMultipleCountriesIsEmpty()
    {
        $this->assertEquals("", $this->geocoder->getDescriptionForNumber(self::$NANPA_TollFree, 'en_US'));
    }
    public function testGetDescriptionForNumber_en_US()
    {
        $ca = $this->geocoder->getDescriptionForNumber(self::$US_Number1, "en_US");
        $this->assertEquals("CA", $ca);
        $this->assertEquals("Mountain View, CA", $this->geocoder->getDescriptionForNumber(self::$US_Number2, "en_US"));
        $this->assertEquals("New York, NY", $this->geocoder->getDescriptionForNumber(self::$US_Number3, "en_US"));
    }
    public function testGetDescriptionForKoreanNumber()
    {
        $this->assertEquals("Seoul", $this->geocoder->getDescriptionForNumber(self::$KO_Number1, "en"));
        $this->assertEquals("Incheon", $this->geocoder->getDescriptionForNumber(self::$KO_Number2, "en"));
        $this->assertEquals("Jeju", $this->geocoder->getDescriptionForNumber(self::$KO_Number3, "en"));
        $this->assertEquals(
            pack('H*', 'ec849c') . pack('H*', 'ec9ab8'),
            $this->geocoder->getDescriptionForNumber(self::$KO_Number1, "ko")
        );
        $this->assertEquals(
            pack('H*', 'ec9db8') . pack('H*', 'ecb29c'),
            $this->geocoder->getDescriptionForNumber(self::$KO_Number2, "ko")
        );
    }
    public function testGetDescriptionForArgentinianMobileNumber()
    {
        $this->assertEquals("La Plata", $this->geocoder->getDescriptionForNumber(self::$AR_MobileNumber, "en"));
    }
    public function testGetDescriptionForFallBack()
    {
        $this->assertEquals("Kalifornien", $this->geocoder->getDescriptionForNumber(self::$US_Number1, "de"));
        $this->assertEquals("New York, NY", $this->geocoder->getDescriptionForNumber(self::$US_Number3, "de"));
        $this->assertEquals("CA", $this->geocoder->getDescriptionForNumber(self::$US_Number1, "it"));
        $this->assertEquals(
            pack('H*', 'eb8c80') . pack('H*', 'ed959c') . pack('H*', 'ebafbc') . pack('H*', 'eab5ad'),
            $this->geocoder->getDescriptionForNumber(self::$KO_Number3, "ko")
        );
    }
    public function testGetDescriptionForNumberWithUserRegion()
    {
        $this->assertEquals(
            "Estados Unidos",
            $this->geocoder->getDescriptionForNumber(self::$US_Number1, "es_ES", "IT")
        );
        $this->assertEquals(
            "Estados Unidos",
            $this->geocoder->getDescriptionForNumber(self::$US_Number1, "es_ES", "ZZ")
        );
        $this->assertEquals("Kalifornien", $this->geocoder->getDescriptionForNumber(self::$US_Number1, "de", "US"));
        $this->assertEquals("CA", $this->geocoder->getDescriptionForNumber(self::$US_Number1, "fr", "US"));
        $this->assertEquals("", $this->geocoder->getDescriptionForNumber(self::$US_InvalidNumber, "en", "US"));
    }
    public function testGetDescriptionForInvalidNumber()
    {
        $this->assertEquals("", $this->geocoder->getDescriptionForNumber(self::$KO_InvalidNumber, "en"));
        $this->assertEquals("", $this->geocoder->getDescriptionForNumber(self::$US_InvalidNumber, "en"));
    }
}
