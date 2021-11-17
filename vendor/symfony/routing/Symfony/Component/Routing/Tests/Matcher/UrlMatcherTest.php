<?php
namespace Symfony\Component\Routing\Tests\Matcher;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
class UrlMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testNoMethodSoAllowed()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo'));
        $matcher = new UrlMatcher($coll, new RequestContext());
        $matcher->match('/foo');
    }
    public function testMethodNotAllowed()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo', array(), array('_method' => 'post')));
        $matcher = new UrlMatcher($coll, new RequestContext());
        try {
            $matcher->match('/foo');
            $this->fail();
        } catch (MethodNotAllowedException $e) {
            $this->assertEquals(array('POST'), $e->getAllowedMethods());
        }
    }
    public function testHeadAllowedWhenRequirementContainsGet()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo', array(), array('_method' => 'get')));
        $matcher = new UrlMatcher($coll, new RequestContext('', 'head'));
        $matcher->match('/foo');
    }
    public function testMethodNotAllowedAggregatesAllowedMethods()
    {
        $coll = new RouteCollection();
        $coll->add('foo1', new Route('/foo', array(), array('_method' => 'post')));
        $coll->add('foo2', new Route('/foo', array(), array('_method' => 'put|delete')));
        $matcher = new UrlMatcher($coll, new RequestContext());
        try {
            $matcher->match('/foo');
            $this->fail();
        } catch (MethodNotAllowedException $e) {
            $this->assertEquals(array('POST', 'PUT', 'DELETE'), $e->getAllowedMethods());
        }
    }
    public function testMatch()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo/{bar}'));
        $matcher = new UrlMatcher($collection, new RequestContext());
        try {
            $matcher->match('/no-match');
            $this->fail();
        } catch (ResourceNotFoundException $e) {
        }
        $this->assertEquals(array('_route' => 'foo', 'bar' => 'baz'), $matcher->match('/foo/baz'));
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo/{bar}', array('def' => 'test')));
        $matcher = new UrlMatcher($collection, new RequestContext());
        $this->assertEquals(array('_route' => 'foo', 'bar' => 'baz', 'def' => 'test'), $matcher->match('/foo/baz'));
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo', array(), array('_method' => 'GET|head')));
        $matcher = new UrlMatcher($collection, new RequestContext());
        $this->assertInternalType('array', $matcher->match('/foo'));
        $matcher = new UrlMatcher($collection, new RequestContext('', 'post'));
        try {
            $matcher->match('/foo');
            $this->fail();
        } catch (MethodNotAllowedException $e) {
        }
        $matcher = new UrlMatcher($collection, new RequestContext());
        $this->assertInternalType('array', $matcher->match('/foo'));
        $matcher = new UrlMatcher($collection, new RequestContext('', 'head'));
        $this->assertInternalType('array', $matcher->match('/foo'));
        $collection = new RouteCollection();
        $collection->add('bar', new Route('/{bar}/foo', array('bar' => 'bar'), array('bar' => 'foo|bar')));
        $matcher = new UrlMatcher($collection, new RequestContext());
        $this->assertEquals(array('_route' => 'bar', 'bar' => 'bar'), $matcher->match('/bar/foo'));
        $this->assertEquals(array('_route' => 'bar', 'bar' => 'foo'), $matcher->match('/foo/foo'));
        $collection = new RouteCollection();
        $collection->add('bar', new Route('/{bar}', array('bar' => 'bar'), array('bar' => 'foo|bar')));
        $matcher = new UrlMatcher($collection, new RequestContext());
        $this->assertEquals(array('_route' => 'bar', 'bar' => 'foo'), $matcher->match('/foo'));
        $this->assertEquals(array('_route' => 'bar', 'bar' => 'bar'), $matcher->match('/'));
        $collection = new RouteCollection();
        $collection->add('bar', new Route('/{foo}/{bar}', array('foo' => 'foo', 'bar' => 'bar'), array()));
        $matcher = new UrlMatcher($collection, new RequestContext());
        $this->assertEquals(array('_route' => 'bar', 'foo' => 'foo', 'bar' => 'bar'), $matcher->match('/'));
        $this->assertEquals(array('_route' => 'bar', 'foo' => 'a', 'bar' => 'bar'), $matcher->match('/a'));
        $this->assertEquals(array('_route' => 'bar', 'foo' => 'a', 'bar' => 'b'), $matcher->match('/a/b'));
    }
    public function testMatchWithPrefixes()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/{foo}'));
        $collection->addPrefix('/b');
        $collection->addPrefix('/a');
        $matcher = new UrlMatcher($collection, new RequestContext());
        $this->assertEquals(array('_route' => 'foo', 'foo' => 'foo'), $matcher->match('/a/b/foo'));
    }
    public function testMatchWithDynamicPrefix()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/{foo}'));
        $collection->addPrefix('/b');
        $collection->addPrefix('/{_locale}');
        $matcher = new UrlMatcher($collection, new RequestContext());
        $this->assertEquals(array('_locale' => 'fr', '_route' => 'foo', 'foo' => 'foo'), $matcher->match('/fr/b/foo'));
    }
    public function testMatchSpecialRouteName()
    {
        $collection = new RouteCollection();
        $collection->add('$péß^a|', new Route('/bar'));
        $matcher = new UrlMatcher($collection, new RequestContext());
        $this->assertEquals(array('_route' => '$péß^a|'), $matcher->match('/bar'));
    }
    public function testMatchNonAlpha()
    {
        $collection = new RouteCollection();
        $chars = '!"$%éà &\'()*+,./:;<=>@ABCDEFGHIJKLMNOPQRSTUVWXYZ\\[]^_`abcdefghijklmnopqrstuvwxyz{|}~-';
        $collection->add('foo', new Route('/{foo}/bar', array(), array('foo' => '['.preg_quote($chars).']+')));
        $matcher = new UrlMatcher($collection, new RequestContext());
        $this->assertEquals(array('_route' => 'foo', 'foo' => $chars), $matcher->match('/'.rawurlencode($chars).'/bar'));
        $this->assertEquals(array('_route' => 'foo', 'foo' => $chars), $matcher->match('/'.strtr($chars, array('%' => '%25')).'/bar'));
    }
    public function testMatchWithDotMetacharacterInRequirements()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/{foo}/bar', array(), array('foo' => '.+')));
        $matcher = new UrlMatcher($collection, new RequestContext());
        $this->assertEquals(array('_route' => 'foo', 'foo' => "\n"), $matcher->match('/'.urlencode("\n").'/bar'), 'linefeed character is matched');
    }
    public function testMatchOverriddenRoute()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo'));
        $collection1 = new RouteCollection();
        $collection1->add('foo', new Route('/foo1'));
        $collection->addCollection($collection1);
        $matcher = new UrlMatcher($collection, new RequestContext());
        $this->assertEquals(array('_route' => 'foo'), $matcher->match('/foo1'));
        $this->setExpectedException('Symfony\Component\Routing\Exception\ResourceNotFoundException');
        $this->assertEquals(array(), $matcher->match('/foo'));
    }
    public function testMatchRegression()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo/{foo}'));
        $coll->add('bar', new Route('/foo/bar/{foo}'));
        $matcher = new UrlMatcher($coll, new RequestContext());
        $this->assertEquals(array('foo' => 'bar', '_route' => 'bar'), $matcher->match('/foo/bar/bar'));
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/{bar}'));
        $matcher = new UrlMatcher($collection, new RequestContext());
        try {
            $matcher->match('/');
            $this->fail();
        } catch (ResourceNotFoundException $e) {
        }
    }
    public function testDefaultRequirementForOptionalVariables()
    {
        $coll = new RouteCollection();
        $coll->add('test', new Route('/{page}.{_format}', array('page' => 'index', '_format' => 'html')));
        $matcher = new UrlMatcher($coll, new RequestContext());
        $this->assertEquals(array('page' => 'my-page', '_format' => 'xml', '_route' => 'test'), $matcher->match('/my-page.xml'));
    }
    public function testMatchingIsEager()
    {
        $coll = new RouteCollection();
        $coll->add('test', new Route('/{foo}-{bar}-', array(), array('foo' => '.+', 'bar' => '.+')));
        $matcher = new UrlMatcher($coll, new RequestContext());
        $this->assertEquals(array('foo' => 'text1-text2-text3', 'bar' => 'text4', '_route' => 'test'), $matcher->match('/text1-text2-text3-text4-'));
    }
    public function testAdjacentVariables()
    {
        $coll = new RouteCollection();
        $coll->add('test', new Route('/{w}{x}{y}{z}.{_format}', array('z' => 'default-z', '_format' => 'html'), array('y' => 'y|Y')));
        $matcher = new UrlMatcher($coll, new RequestContext());
        $this->assertEquals(array('w' => 'wwwww', 'x' => 'x', 'y' => 'Y', 'z' => 'Z', '_format' => 'xml', '_route' => 'test'), $matcher->match('/wwwwwxYZ.xml'));
        $this->assertEquals(array('w' => 'wwwww', 'x' => 'x', 'y' => 'y', 'z' => 'ZZZ', '_format' => 'html', '_route' => 'test'), $matcher->match('/wwwwwxyZZZ'));
        $this->assertEquals(array('w' => 'wwwww', 'x' => 'x', 'y' => 'y', 'z' => 'default-z', '_format' => 'html', '_route' => 'test'), $matcher->match('/wwwwwxy'));
        $this->setExpectedException('Symfony\Component\Routing\Exception\ResourceNotFoundException');
        $matcher->match('/wxy.html');
    }
    public function testOptionalVariableWithNoRealSeparator()
    {
        $coll = new RouteCollection();
        $coll->add('test', new Route('/get{what}', array('what' => 'All')));
        $matcher = new UrlMatcher($coll, new RequestContext());
        $this->assertEquals(array('what' => 'All', '_route' => 'test'), $matcher->match('/get'));
        $this->assertEquals(array('what' => 'Sites', '_route' => 'test'), $matcher->match('/getSites'));
        $this->setExpectedException('Symfony\Component\Routing\Exception\ResourceNotFoundException');
        $matcher->match('/ge');
    }
    public function testRequiredVariableWithNoRealSeparator()
    {
        $coll = new RouteCollection();
        $coll->add('test', new Route('/get{what}Suffix'));
        $matcher = new UrlMatcher($coll, new RequestContext());
        $this->assertEquals(array('what' => 'Sites', '_route' => 'test'), $matcher->match('/getSitesSuffix'));
    }
    public function testDefaultRequirementOfVariable()
    {
        $coll = new RouteCollection();
        $coll->add('test', new Route('/{page}.{_format}'));
        $matcher = new UrlMatcher($coll, new RequestContext());
        $this->assertEquals(array('page' => 'index', '_format' => 'mobile.html', '_route' => 'test'), $matcher->match('/index.mobile.html'));
    }
    public function testDefaultRequirementOfVariableDisallowsSlash()
    {
        $coll = new RouteCollection();
        $coll->add('test', new Route('/{page}.{_format}'));
        $matcher = new UrlMatcher($coll, new RequestContext());
        $matcher->match('/index.sl/ash');
    }
    public function testDefaultRequirementOfVariableDisallowsNextSeparator()
    {
        $coll = new RouteCollection();
        $coll->add('test', new Route('/{page}.{_format}', array(), array('_format' => 'html|xml')));
        $matcher = new UrlMatcher($coll, new RequestContext());
        $matcher->match('/do.t.html');
    }
    public function testSchemeRequirementBC()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo', array(), array('_scheme' => 'https')));
        $matcher = new UrlMatcher($coll, new RequestContext());
        $matcher->match('/foo');
    }
    public function testSchemeRequirement()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo', array(), array(), array(), '', array('https')));
        $matcher = new UrlMatcher($coll, new RequestContext());
        $matcher->match('/foo');
    }
    public function testCondition()
    {
        $coll = new RouteCollection();
        $route = new Route('/foo');
        $route->setCondition('context.getMethod() == "POST"');
        $coll->add('foo', $route);
        $matcher = new UrlMatcher($coll, new RequestContext());
        $matcher->match('/foo');
    }
    public function testDecodeOnce()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo/{foo}'));
        $matcher = new UrlMatcher($coll, new RequestContext());
        $this->assertEquals(array('foo' => 'bar%23', '_route' => 'foo'), $matcher->match('/foo/bar%2523'));
    }
    public function testCannotRelyOnPrefix()
    {
        $coll = new RouteCollection();
        $subColl = new RouteCollection();
        $subColl->add('bar', new Route('/bar'));
        $subColl->addPrefix('/prefix');
        $subColl->get('bar')->setPath('/new');
        $coll->addCollection($subColl);
        $matcher = new UrlMatcher($coll, new RequestContext());
        $this->assertEquals(array('_route' => 'bar'), $matcher->match('/new'));
    }
    public function testWithHost()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo/{foo}', array(), array(), array(), '{locale}.example.com'));
        $matcher = new UrlMatcher($coll, new RequestContext('', 'GET', 'en.example.com'));
        $this->assertEquals(array('foo' => 'bar', '_route' => 'foo', 'locale' => 'en'), $matcher->match('/foo/bar'));
    }
    public function testWithHostOnRouteCollection()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo/{foo}'));
        $coll->add('bar', new Route('/bar/{foo}', array(), array(), array(), '{locale}.example.net'));
        $coll->setHost('{locale}.example.com');
        $matcher = new UrlMatcher($coll, new RequestContext('', 'GET', 'en.example.com'));
        $this->assertEquals(array('foo' => 'bar', '_route' => 'foo', 'locale' => 'en'), $matcher->match('/foo/bar'));
        $matcher = new UrlMatcher($coll, new RequestContext('', 'GET', 'en.example.com'));
        $this->assertEquals(array('foo' => 'bar', '_route' => 'bar', 'locale' => 'en'), $matcher->match('/bar/bar'));
    }
    public function testWithOutHostHostDoesNotMatch()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo/{foo}', array(), array(), array(), '{locale}.example.com'));
        $matcher = new UrlMatcher($coll, new RequestContext('', 'GET', 'example.com'));
        $matcher->match('/foo/bar');
    }
    public function testPathIsCaseSensitive()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/locale', array(), array('locale' => 'EN|FR|DE')));
        $matcher = new UrlMatcher($coll, new RequestContext());
        $matcher->match('/en');
    }
    public function testHostIsCaseInsensitive()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/', array(), array('locale' => 'EN|FR|DE'), array(), '{locale}.example.com'));
        $matcher = new UrlMatcher($coll, new RequestContext('', 'GET', 'en.example.com'));
        $this->assertEquals(array('_route' => 'foo', 'locale' => 'en'), $matcher->match('/'));
    }
}
