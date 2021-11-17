<?php
namespace Psy\Presenter;
interface Presenter
{
    const VERBOSE = 1;
    public function canPresent($value);
    public function presentRef($value);
    public function present($value, $depth = null, $options = 0);
}
