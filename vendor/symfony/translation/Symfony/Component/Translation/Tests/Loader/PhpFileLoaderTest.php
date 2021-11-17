<?php
namespace Symfony\Component\Translation\Tests\Loader;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Config\Resource\FileResource;
class PhpFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $loader = new PhpFileLoader();
        $resource = __DIR__.'/../fixtures/resources.php';
        $catalogue = $loader->load($resource, 'en', 'domain1');
        $this->assertEquals(array('foo' => 'bar'), $catalogue->all('domain1'));
        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals(array(new FileResource($resource)), $catalogue->getResources());
    }
    public function testLoadNonExistingResource()
    {
        $loader = new PhpFileLoader();
        $resource = __DIR__.'/../fixtures/non-existing.php';
        $loader->load($resource, 'en', 'domain1');
    }
    public function testLoadThrowsAnExceptionIfFileNotLocal()
    {
        $loader = new PhpFileLoader();
        $resource = 'http:
        $loader->load($resource, 'en', 'domain1');
    }
}
