<?php
namespace Symfony\Component\Translation\Tests\Loader;
use Symfony\Component\Translation\Loader\CsvFileLoader;
use Symfony\Component\Config\Resource\FileResource;
class CsvFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $loader = new CsvFileLoader();
        $resource = __DIR__.'/../fixtures/resources.csv';
        $catalogue = $loader->load($resource, 'en', 'domain1');
        $this->assertEquals(array('foo' => 'bar'), $catalogue->all('domain1'));
        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals(array(new FileResource($resource)), $catalogue->getResources());
    }
    public function testLoadDoesNothingIfEmpty()
    {
        $loader = new CsvFileLoader();
        $resource = __DIR__.'/../fixtures/empty.csv';
        $catalogue = $loader->load($resource, 'en', 'domain1');
        $this->assertEquals(array(), $catalogue->all('domain1'));
        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals(array(new FileResource($resource)), $catalogue->getResources());
    }
    public function testLoadNonExistingResource()
    {
        $loader = new CsvFileLoader();
        $resource = __DIR__.'/../fixtures/not-exists.csv';
        $loader->load($resource, 'en', 'domain1');
    }
    public function testLoadNonLocalResource()
    {
        $loader = new CsvFileLoader();
        $resource = 'http:
        $loader->load($resource, 'en', 'domain1');
    }
}
