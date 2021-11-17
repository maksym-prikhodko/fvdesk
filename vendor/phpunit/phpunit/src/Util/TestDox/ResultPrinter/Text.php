<?php
class PHPUnit_Util_TestDox_ResultPrinter_Text extends PHPUnit_Util_TestDox_ResultPrinter
{
    protected function startClass($name)
    {
        $this->write($this->currentTestClassPrettified . "\n");
    }
    protected function onTest($name, $success = true)
    {
        if ($success) {
            $this->write(' [x] ');
        } else {
            $this->write(' [ ] ');
        }
        $this->write($name . "\n");
    }
    protected function endClass($name)
    {
        $this->write("\n");
    }
}
