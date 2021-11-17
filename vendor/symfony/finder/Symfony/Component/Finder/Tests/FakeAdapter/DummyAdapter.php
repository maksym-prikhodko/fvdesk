<?php
namespace Symfony\Component\Finder\Tests\FakeAdapter;
use Symfony\Component\Finder\Adapter\AbstractAdapter;
class DummyAdapter extends AbstractAdapter
{
    private $iterator;
    public function __construct(\Iterator $iterator)
    {
        $this->iterator = $iterator;
    }
    public function searchInDirectory($dir)
    {
        return $this->iterator;
    }
    public function getName()
    {
        return 'yes';
    }
    protected function canBeUsed()
    {
        return true;
    }
}
