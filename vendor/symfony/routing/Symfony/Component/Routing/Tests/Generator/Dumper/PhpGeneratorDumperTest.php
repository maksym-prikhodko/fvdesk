<?php
namespace Symfony\Component\Routing\Tests\Generator\Dumper;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Generator\Dumper\PhpGeneratorDumper;
use Symfony\Component\Routing\RequestContext;
class PhpGeneratorDumperTest extends \PHPUnit_Framework_TestCase
{
    private $routeCollection;
    private $generatorDumper;
    private $testTmpFilepath;
    protected function setUp()
    {
        parent::setUp();
        $this->routeCollection = new RouteCollection();
        $this->generatorDumper = new PhpGeneratorDumper($this->routeCollection);
        $this->testTmpFilepath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'php_generator.php';
        @unlink($this->testTmpFilepath);
    }
    protected function tearDown()
    {
        parent::tearDown();
        @unlink($this->testTmpFilepath);
        $this->routeCollection = null;
        $this->generatorDumper = null;
        $this->testTmpFilepath = null;
    }
    public function testDumpWithRoutes()
    {
        $this->routeCollection->add('Test', new Route('/testing/{foo}'));
        $this->routeCollection->add('Test2', new Route('/testing2'));
        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump());
        include $this->testTmpFilepath;
        $projectUrlGenerator = new \ProjectUrlGenerator(new RequestContext('/app.php'));
        $absoluteUrlWithParameter = $projectUrlGenerator->generate('Test', array('foo' => 'bar'), true);
        $absoluteUrlWithoutParameter = $projectUrlGenerator->generate('Test2', array(), true);
        $relativeUrlWithParameter = $projectUrlGenerator->generate('Test', array('foo' => 'bar'), false);
        $relativeUrlWithoutParameter = $projectUrlGenerator->generate('Test2', array(), false);
        $this->assertEquals($absoluteUrlWithParameter, 'http:
        $this->assertEquals($absoluteUrlWithoutParameter, 'http:
        $this->assertEquals($relativeUrlWithParameter, '/app.php/testing/bar');
        $this->assertEquals($relativeUrlWithoutParameter, '/app.php/testing2');
    }
    public function testDumpWithoutRoutes()
    {
        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump(array('class' => 'WithoutRoutesUrlGenerator')));
        include $this->testTmpFilepath;
        $projectUrlGenerator = new \WithoutRoutesUrlGenerator(new RequestContext('/app.php'));
        $projectUrlGenerator->generate('Test', array());
    }
    public function testGenerateNonExistingRoute()
    {
        $this->routeCollection->add('Test', new Route('/test'));
        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump(array('class' => 'NonExistingRoutesUrlGenerator')));
        include $this->testTmpFilepath;
        $projectUrlGenerator = new \NonExistingRoutesUrlGenerator(new RequestContext());
        $url = $projectUrlGenerator->generate('NonExisting', array());
    }
    public function testDumpForRouteWithDefaults()
    {
        $this->routeCollection->add('Test', new Route('/testing/{foo}', array('foo' => 'bar')));
        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump(array('class' => 'DefaultRoutesUrlGenerator')));
        include $this->testTmpFilepath;
        $projectUrlGenerator = new \DefaultRoutesUrlGenerator(new RequestContext());
        $url = $projectUrlGenerator->generate('Test', array());
        $this->assertEquals($url, '/testing');
    }
    public function testDumpWithSchemeRequirement()
    {
        $this->routeCollection->add('Test1', new Route('/testing', array(), array(), array(), '', array('ftp', 'https')));
        $this->routeCollection->add('Test2', new Route('/testing_bc', array(), array('_scheme' => 'https'))); 
        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump(array('class' => 'SchemeUrlGenerator')));
        include $this->testTmpFilepath;
        $projectUrlGenerator = new \SchemeUrlGenerator(new RequestContext('/app.php'));
        $absoluteUrl = $projectUrlGenerator->generate('Test1', array(), true);
        $absoluteUrlBC = $projectUrlGenerator->generate('Test2', array(), true);
        $relativeUrl = $projectUrlGenerator->generate('Test1', array(), false);
        $relativeUrlBC = $projectUrlGenerator->generate('Test2', array(), false);
        $this->assertEquals($absoluteUrl, 'ftp:
        $this->assertEquals($absoluteUrlBC, 'https:
        $this->assertEquals($relativeUrl, 'ftp:
        $this->assertEquals($relativeUrlBC, 'https:
        $projectUrlGenerator = new \SchemeUrlGenerator(new RequestContext('/app.php', 'GET', 'localhost', 'https'));
        $absoluteUrl = $projectUrlGenerator->generate('Test1', array(), true);
        $absoluteUrlBC = $projectUrlGenerator->generate('Test2', array(), true);
        $relativeUrl = $projectUrlGenerator->generate('Test1', array(), false);
        $relativeUrlBC = $projectUrlGenerator->generate('Test2', array(), false);
        $this->assertEquals($absoluteUrl, 'https:
        $this->assertEquals($absoluteUrlBC, 'https:
        $this->assertEquals($relativeUrl, '/app.php/testing');
        $this->assertEquals($relativeUrlBC, '/app.php/testing_bc');
    }
}
