<?php
class PHPUnit_Util_TestDox_ResultPrinter_HTML extends PHPUnit_Util_TestDox_ResultPrinter
{
    protected $printsHTML = true;
    protected function startRun()
    {
        $this->write('<html><body>');
    }
    protected function startClass($name)
    {
        $this->write(
            '<h2 id="' . $name . '">' . $this->currentTestClassPrettified .
            '</h2><ul>'
        );
    }
    protected function onTest($name, $success = true)
    {
        if (!$success) {
            $strikeOpen  = '<span style="text-decoration:line-through;">';
            $strikeClose = '</span>';
        } else {
            $strikeOpen  = '';
            $strikeClose = '';
        }
        $this->write('<li>' . $strikeOpen . $name . $strikeClose . '</li>');
    }
    protected function endClass($name)
    {
        $this->write('</ul>');
    }
    protected function endRun()
    {
        $this->write('</body></html>');
    }
}
