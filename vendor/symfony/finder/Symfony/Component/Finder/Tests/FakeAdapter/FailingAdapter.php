<?php
namespace Symfony\Component\Finder\Tests\FakeAdapter;
use Symfony\Component\Finder\Adapter\AbstractAdapter;
use Symfony\Component\Finder\Exception\AdapterFailureException;
class FailingAdapter extends AbstractAdapter
{
    public function searchInDirectory($dir)
    {
        throw new AdapterFailureException($this);
    }
    public function getName()
    {
        return 'failing';
    }
    protected function canBeUsed()
    {
        return true;
    }
}
