<?php
namespace Symfony\Component\VarDumper\Dumper;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Cloner\DumperInterface;
abstract class AbstractDumper implements DataDumperInterface, DumperInterface
{
    public static $defaultOutput = 'php:
    protected $line = '';
    protected $lineDumper;
    protected $outputStream;
    protected $decimalPoint; 
    protected $indentPad = '  ';
    private $charset;
    private $charsetConverter;
    public function __construct($output = null, $charset = null)
    {
        $this->setCharset($charset ?: ini_get('php.output_encoding') ?: ini_get('default_charset') ?: 'UTF-8');
        $this->decimalPoint = (string) 0.5;
        $this->decimalPoint = $this->decimalPoint[1];
        $this->setOutput($output ?: static::$defaultOutput);
        if (!$output && is_string(static::$defaultOutput)) {
            static::$defaultOutput = $this->outputStream;
        }
    }
    public function setOutput($output)
    {
        $prev = null !== $this->outputStream ? $this->outputStream : $this->lineDumper;
        if (is_callable($output)) {
            $this->outputStream = null;
            $this->lineDumper = $output;
        } else {
            if (is_string($output)) {
                $output = fopen($output, 'wb');
            }
            $this->outputStream = $output;
            $this->lineDumper = array($this, 'echoLine');
        }
        return $prev;
    }
    public function setCharset($charset)
    {
        $prev = $this->charset;
        $this->charsetConverter = 'fallback';
        $charset = strtoupper($charset);
        $charset = null === $charset || 'UTF-8' === $charset || 'UTF8' === $charset ? 'CP1252' : $charset;
        $supported = true;
        set_error_handler(function () use (&$supported) {$supported = false;});
        if (function_exists('mb_encoding_aliases') && mb_encoding_aliases($charset)) {
            $this->charset = $charset;
            $this->charsetConverter = 'mbstring';
        } elseif (function_exists('iconv')) {
            $supported = true;
            iconv($charset, 'UTF-8', '');
            if ($supported) {
                $this->charset = $charset;
                $this->charsetConverter = 'iconv';
            }
        }
        if ('fallback' === $this->charsetConverter) {
            $this->charset = 'ISO-8859-1';
        }
        restore_error_handler();
        return $prev;
    }
    public function setIndentPad($pad)
    {
        $prev = $this->indentPad;
        $this->indentPad = $pad;
        return $prev;
    }
    public function dump(Data $data, $output = null)
    {
        $exception = null;
        if ($output) {
            $prevOutput = $this->setOutput($output);
        }
        try {
            $data->dump($this);
            $this->dumpLine(-1);
        } catch (\Exception $exception) {
        }
        if ($output) {
            $this->setOutput($prevOutput);
        }
        if (null !== $exception) {
            throw $exception;
        }
    }
    protected function dumpLine($depth)
    {
        call_user_func($this->lineDumper, $this->line, $depth, $this->indentPad);
        $this->line = '';
    }
    protected function echoLine($line, $depth, $indentPad)
    {
        if (-1 !== $depth) {
            fwrite($this->outputStream, str_repeat($indentPad, $depth).$line."\n");
        }
    }
    protected function utf8Encode($s)
    {
        if ('mbstring' === $this->charsetConverter) {
            return mb_convert_encoding($s, 'UTF-8', mb_check_encoding($s, $this->charset) ? $this->charset : '8bit');
        }
        if ('iconv' === $this->charsetConverter) {
            $valid = true;
            set_error_handler(function () use (&$valid) {$valid = false;});
            $c = iconv($this->charset, 'UTF-8', $s);
            restore_error_handler();
            if ($valid) {
                return $c;
            }
        }
        $s .= $s;
        $len = strlen($s);
        for ($i = $len >> 1, $j = 0; $i < $len; ++$i, ++$j) {
            switch (true) {
                case $s[$i] < "\x80":
                    $s[$j] = $s[$i];
                    break;
                case $s[$i] < "\xC0":
                    $s[$j] = "\xC2";
                    $s[++$j] = $s[$i];
                    break;
                default:
                    $s[$j] = "\xC3";
                    $s[++$j] = chr(ord($s[$i]) - 64);
                    break;
            }
        }
        return substr($s, 0, $j);
    }
}
