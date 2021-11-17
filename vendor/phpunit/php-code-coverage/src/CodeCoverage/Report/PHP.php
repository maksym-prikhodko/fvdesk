<?php
class PHP_CodeCoverage_Report_PHP
{
    public function process(PHP_CodeCoverage $coverage, $target = null)
    {
        $filter = $coverage->filter();
        $output = sprintf(
            '<?php
$coverage = new PHP_CodeCoverage;
$coverage->setData(%s);
$coverage->setTests(%s);
$filter = $coverage->filter();
$filter->setBlacklistedFiles(%s);
$filter->setWhitelistedFiles(%s);
return $coverage;',
            var_export($coverage->getData(true), 1),
            var_export($coverage->getTests(), 1),
            var_export($filter->getBlacklistedFiles(), 1),
            var_export($filter->getWhitelistedFiles(), 1)
        );
        if ($target !== null) {
            return file_put_contents($target, $output);
        } else {
            return $output;
        }
    }
}
