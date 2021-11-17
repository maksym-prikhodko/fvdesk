<?php
class PHPUnit_Util_Printer
{
    protected $autoFlush = false;
    protected $out;
    protected $outTarget;
    protected $printsHTML = false;
    public function __construct($out = null)
    {
        if ($out !== null) {
            if (is_string($out)) {
                if (strpos($out, 'socket:
                    $out = explode(':', str_replace('socket:
                    if (sizeof($out) != 2) {
                        throw new PHPUnit_Framework_Exception;
                    }
                    $this->out = fsockopen($out[0], $out[1]);
                } else {
                    if (strpos($out, 'php:
                        !is_dir(dirname($out))) {
                        mkdir(dirname($out), 0777, true);
                    }
                    $this->out = fopen($out, 'wt');
                }
                $this->outTarget = $out;
            } else {
                $this->out = $out;
            }
        }
    }
    public function flush()
    {
        if ($this->out && strncmp($this->outTarget, 'php:
            fclose($this->out);
        }
        if ($this->printsHTML === true &&
            $this->outTarget !== null &&
            strpos($this->outTarget, 'php:
            strpos($this->outTarget, 'socket:
            extension_loaded('tidy')) {
            file_put_contents(
                $this->outTarget,
                tidy_repair_file(
                    $this->outTarget,
                    array('indent' => true, 'wrap' => 0),
                    'utf8'
                )
            );
        }
    }
    public function incrementalFlush()
    {
        if ($this->out) {
            fflush($this->out);
        } else {
            flush();
        }
    }
    public function write($buffer)
    {
        if ($this->out) {
            fwrite($this->out, $buffer);
            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        } else {
            if (PHP_SAPI != 'cli') {
                $buffer = htmlspecialchars($buffer);
            }
            print $buffer;
            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        }
    }
    public function getAutoFlush()
    {
        return $this->autoFlush;
    }
    public function setAutoFlush($autoFlush)
    {
        if (is_bool($autoFlush)) {
            $this->autoFlush = $autoFlush;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }
}
