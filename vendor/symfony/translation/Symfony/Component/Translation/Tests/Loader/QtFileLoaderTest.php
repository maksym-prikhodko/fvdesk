<?php
namespace Symfony\Component\Translation\Tests\Loader;
use Symfony\Component\Translation\Loader\QtFileLoader;
use Symfony\Component\Config\Resource\FileResource;
class QtFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $loader = new QtFileLoader();
        $resource = __DIR__.'/../fixtures/resources.ts';
        $catalogue = $loader->load($resource, 'en', 'resources');
        $this->assertEquals(array('foo' => 'bar'), $catalogue->all('resources'));
        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals(array(new FileResource($resource)), $catalogue->getResources());
    }
    public function testLoadNonExistingResource()
    {
        $loader = new QtFileLoader();
        $resource = __DIR__.'/../fixtures/non-existing.ts';
        $loader->load($resource, 'en', 'domain1');
    }
    public function testLoadNonLocalResource()
    {
        $loader = new QtFileLoader();
        $resource = 'http:
        $loader->load($resource, 'en', 'domain1');
    }
    public function testLoadInvalidResource()
    {
        $loader = new QtFileLoader();
        $resource = __DIR__.'/../fixtures/invalid-xml-resources.xlf';
        $loader->load($resource, 'en', 'domain1');
    }
    public function testLoadEmptyResource()
    {
        $loader = new QtFileLoader();
        $resource = __DIR__.'/../fixtures/empty.xlf';
        $this->setExpectedException('Symfony\Component\Translation\Exception\InvalidResourceException', sprintf('Unable to load "%s".', $resource));
        $loader->load($resource, 'en', 'domain1');
    }
}
