<?php
namespace Psy\Test\Presenter;
use Psy\Presenter\ClosurePresenter;
use Psy\Presenter\ObjectPresenter;
use Psy\Presenter\PresenterManager;
use Psy\Presenter\ScalarPresenter;
class ClosurePresenterTest extends \PHPUnit_Framework_TestCase
{
    private $presenter;
    private $manager;
    public function setUp()
    {
        $this->presenter = new ClosurePresenter();
        $this->manager   = new PresenterManager();
        $this->manager->addPresenter(new ScalarPresenter());
        $this->manager->addPresenter(new ObjectPresenter());
        $this->manager->addPresenter($this->presenter);
    }
    public function testPresent($closure, $expect)
    {
        $this->assertEquals($expect, $this->presenter->present($closure));
    }
    public function testPresentRef($closure, $expect)
    {
        $this->assertEquals($expect, $this->presenter->presentRef($closure));
    }
    public function presentData()
    {
        $null = null;
        $eol  = version_compare(PHP_VERSION, '5.4.3', '>=') ? '<const>PHP_EOL</const>' : '<string>"\n"</string>';
        return array(
            array(
                function () {
                },
                '<keyword>function</keyword> () { <comment>...</comment> }',
            ),
            array(
                function ($foo) {
                },
                '<keyword>function</keyword> ($<strong>foo</strong>) { <comment>...</comment> }',
            ),
            array(
                function ($foo, $bar = null) {
                },
                '<keyword>function</keyword> ($<strong>foo</strong>, $<strong>bar</strong> = <bool>null</bool>) { <comment>...</comment> }',
            ),
            array(
                function ($foo = "bar") {
                },
                '<keyword>function</keyword> ($<strong>foo</strong> = <string>"bar"</string>) { <comment>...</comment> }',
            ),
            array(
                function ($foo = \PHP_EOL) {
                },
                '<keyword>function</keyword> ($<strong>foo</strong> = ' . $eol . ') { <comment>...</comment> }',
            ),
            array(
                function ($foo) use ($eol, $null) {
                },
                '<keyword>function</keyword> ($<strong>foo</strong>) use ($<strong>eol</strong>, $<strong>null</strong>) { <comment>...</comment> }',
            ),
        );
    }
}
