<?php
namespace Symfony\Component\Finder\Tests\FakeAdapter;
use Symfony\Component\Finder\Adapter\AbstractAdapter;
class UnsupportedAdapter extends AbstractAdapter
{
    public function searchInDirectory($dir)
    {
        return new \ArrayIterator(array());
    }
    public function getName()
    {
        return 'unsupported';
    }
    protected function canBeUsed()
    {
        return false;
    }
}
