<?php
class PHP_CodeCoverage_Driver_HHVM implements PHP_CodeCoverage_Driver
{
    public function __construct()
    {
        if (!defined('HHVM_VERSION')) {
            throw new PHP_CodeCoverage_Exception('This driver requires HHVM');
        }
    }
    public function start()
    {
        fb_enable_code_coverage();
    }
    public function stop()
    {
        $codeCoverage = fb_get_code_coverage(true);
        fb_disable_code_coverage();
        return $codeCoverage;
    }
}
