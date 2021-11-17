<?php
namespace SebastianBergmann\Environment;
class Console
{
    const STDIN  = 0;
    const STDOUT = 1;
    const STDERR = 2;
    public function hasColorSupport()
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            return false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI');
        }
        if (!defined('STDOUT')) {
            return false;
        }
        return $this->isInteractive(STDOUT);
    }
    public function getNumberOfColumns()
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            return 79;
        }
        if (!$this->isInteractive(self::STDIN)) {
            return 80;
        }
        if (preg_match('#\d+ (\d+)#', shell_exec('stty size'), $match) === 1) {
            return (int) $match[1];
        }
        if (preg_match('#columns = (\d+);#', shell_exec('stty'), $match) === 1) {
            return (int) $match[1];
        }
        return 80;
    }
    public function isInteractive($fileDescriptor = self::STDOUT)
    {
        return function_exists('posix_isatty') && @posix_isatty($fileDescriptor);
    }
}
