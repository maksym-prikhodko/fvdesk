<?php
namespace Psy\Presenter;
abstract class RecursivePresenter implements Presenter, PresenterManagerAware
{
    const MAX_DEPTH = 5;
    const INDENT    = '    ';
    protected $manager;
    protected $depth;
    public function setPresenterManager(PresenterManager $manager)
    {
        $this->manager = $manager;
    }
    public function present($value, $depth = null, $options = 0)
    {
        $this->setDepth($depth);
        return $this->presentValue($value, $depth, $options);
    }
    abstract protected function presentValue($value);
    protected function setDepth($depth = null)
    {
        $this->depth = $depth === null ? self::MAX_DEPTH : $depth;
    }
    protected function presentSubValue($value, $options = 0)
    {
        $depth = $this->depth;
        $formatted = $this->manager->present($value, $depth - 1, $options);
        $this->setDepth($depth);
        return $formatted;
    }
    protected function indentValue($value)
    {
        return str_replace(PHP_EOL, PHP_EOL . self::INDENT, $value);
    }
}
