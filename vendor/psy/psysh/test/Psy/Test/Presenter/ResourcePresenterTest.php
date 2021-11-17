<?php
namespace Psy\Test\Presenter;
use Psy\Presenter\ResourcePresenter;
class ResourcePresenterTest extends \PHPUnit_Framework_TestCase
{
    private $presenter;
    public function setUp()
    {
        $this->presenter = new ResourcePresenter();
    }
    public function testPresent()
    {
        $resource = fopen('php:
        $this->assertStringMatchesFormat('<resource>\<STDIO stream <strong>resource #%d</strong>></resource>', $this->presenter->present($resource));
        fclose($resource);
    }
}
