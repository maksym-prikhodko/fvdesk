<?php
namespace Symfony\Component\Translation\Tests\Loader;
use Symfony\Component\Translation\Loader\IcuDatFileLoader;
use Symfony\Component\Config\Resource\FileResource;
class IcuDatFileLoaderTest extends LocalizedTestCase
{
    protected function setUp()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('This test requires intl extension to work.');
        }
    }
    public function testLoadInvalidResource()
    {
        $loader = new IcuDatFileLoader();
        $loader->load(__DIR__.'/../fixtures/resourcebundle/corrupted/resources', 'es', 'domain2');
    }
    public function testDatEnglishLoad()
    {
        $loader = new IcuDatFileLoader();
        $resource = __DIR__.'/../fixtures/resourcebundle/dat/resources';
        $catalogue = $loader->load($resource, 'en', 'domain1');
        $this->assertEquals(array('symfony' => 'Symfony 2 is great'), $catalogue->all('domain1'));
        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals(array(new FileResource($resource.'.dat')), $catalogue->getResources());
    }
    public function testDatFrenchLoad()
    {
        $loader = new IcuDatFileLoader();
        $resource = __DIR__.'/../fixtures/resourcebundle/dat/resources';
        $catalogue = $loader->load($resource, 'fr', 'domain1');
        $this->assertEquals(array('symfony' => 'Symfony 2 est génial'), $catalogue->all('domain1'));
        $this->assertEquals('fr', $catalogue->getLocale());
        $this->assertEquals(array(new FileResource($resource.'.dat')), $catalogue->getResources());
    }
    public function testLoadNonExistingResource()
    {
        $loader = new IcuDatFileLoader();
        $loader->load(__DIR__.'/../fixtures/non-existing.txt', 'en', 'domain1');
    }
}
