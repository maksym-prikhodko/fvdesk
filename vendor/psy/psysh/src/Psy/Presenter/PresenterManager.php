<?php
namespace Psy\Presenter;
class PresenterManager implements Presenter, \IteratorAggregate
{
    protected $presenters = array();
    public function __construct()
    {
        $this->addPresenters(array(
            new ObjectPresenter(), 
            new ArrayPresenter(),
            new ClosurePresenter(),
            new ExceptionPresenter(),
            new ResourcePresenter(),
            new ScalarPresenter(),
        ));
    }
    public function addPresenters(array $presenters)
    {
        foreach ($presenters as $presenter) {
            $this->addPresenter($presenter);
        }
    }
    public function addPresenter(Presenter $presenter)
    {
        $this->removePresenter($presenter);
        if ($presenter instanceof PresenterManagerAware) {
            $presenter->setPresenterManager($this);
        }
        array_unshift($this->presenters, $presenter);
    }
    public function removePresenter(Presenter $presenter)
    {
        foreach ($this->presenters as $i => $p) {
            if ($p === $presenter) {
                unset($this->presenters[$i]);
            }
        }
    }
    public function canPresent($value)
    {
        return $this->getPresenter($value) !== null;
    }
    public function presentRef($value)
    {
        if ($presenter = $this->getPresenter($value)) {
            return $presenter->presentRef($value);
        }
        throw new \InvalidArgumentException(sprintf('Unable to present %s', $value));
    }
    public function present($value, $depth = null, $options = 0)
    {
        if ($presenter = $this->getPresenter($value)) {
            if ($depth === 0) {
                return $presenter->presentRef($value);
            }
            return $presenter->present($value, $depth, $options);
        }
        throw new \InvalidArgumentException(sprintf('Unable to present %s', $value));
    }
    public function getIterator()
    {
        return new \ArrayIterator(array_reverse($this->presenters));
    }
    protected function getPresenter($value)
    {
        foreach ($this->presenters as $presenter) {
            if ($presenter->canPresent($value)) {
                return $presenter;
            }
        }
    }
}
