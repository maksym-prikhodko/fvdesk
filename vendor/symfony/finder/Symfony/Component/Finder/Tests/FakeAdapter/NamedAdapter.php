<?php
namespace Symfony\Component\Finder\Tests\FakeAdapter;
use Symfony\Component\Finder\Adapter\AbstractAdapter;
class NamedAdapter extends AbstractAdapter
{
    private $name;
    public function __construct($name)
    {
        $this->name = $name;
    }
    public function searchInDirectory($dir)
    {
        return new \ArrayIterator(array());
    }
    public function getName()
    {
        return $this->name;
    }
    protected function canBeUsed()
    {
        return true;
    }
}
