<?php
namespace PhpParser;
abstract class CodeTestAbstract extends \PHPUnit_Framework_TestCase
{
    protected function getTests($directory, $fileExtension) {
        $it = new \RecursiveDirectoryIterator($directory);
        $it = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::LEAVES_ONLY);
        $it = new \RegexIterator($it, '(\.' . preg_quote($fileExtension) . '$)');
        $tests = array();
        foreach ($it as $file) {
            $fileName = realpath($file->getPathname());
            $fileContents = file_get_contents($fileName);
            $fileContents = preg_replace_callback(
                '/@@\{(.*?)\}@@/',
                array($this, 'evalCallback'),
                $fileContents
            );
            $parts = array_map('trim', explode('-----', $fileContents));
            $name = array_shift($parts) . ' (' . $fileName . ')';
            foreach (array_chunk($parts, 2) as $chunk) {
                $tests[] = array($name, $chunk[0], $chunk[1]);
            }
        }
        return $tests;
    }
    protected function evalCallback($matches) {
        return eval('return ' . $matches[1] . ';');
    }
    protected function canonicalize($str) {
        $str = trim($str);
        $str = str_replace(array("\r\n", "\r"), "\n", $str);
        return implode("\n", array_map('rtrim', explode("\n", $str)));
    }
}
