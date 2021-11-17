<?php
class PHP_CodeCoverage_Driver_Xdebug implements PHP_CodeCoverage_Driver
{
    public function __construct()
    {
        if (!extension_loaded('xdebug')) {
            throw new PHP_CodeCoverage_Exception('This driver requires Xdebug');
        }
        if (version_compare(phpversion('xdebug'), '2.2.0-dev', '>=') &&
            !ini_get('xdebug.coverage_enable')) {
            throw new PHP_CodeCoverage_Exception(
                'xdebug.coverage_enable=On has to be set in php.ini'
            );
        }
    }
    public function start()
    {
        xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
    }
    public function stop()
    {
        $data = xdebug_get_code_coverage();
        xdebug_stop_code_coverage();
        return $this->cleanup($data);
    }
    private function cleanup(array $data)
    {
        foreach (array_keys($data) as $file) {
            if (isset($data[$file][0])) {
                unset($data[$file][0]);
            }
            if (file_exists($file)) {
                $numLines = $this->getNumberOfLinesInFile($file);
                foreach (array_keys($data[$file]) as $line) {
                    if (isset($data[$file][$line]) && $line > $numLines) {
                        unset($data[$file][$line]);
                    }
                }
            }
        }
        return $data;
    }
    private function getNumberOfLinesInFile($file)
    {
        $buffer = file_get_contents($file);
        $lines  = substr_count($buffer, "\n");
        if (substr($buffer, -1) !== "\n") {
            $lines++;
        }
        return $lines;
    }
}
