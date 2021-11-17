<?php
namespace Psy\Formatter;
use JakubOnderka\PhpConsoleColor\ConsoleColor;
use JakubOnderka\PhpConsoleHighlighter\Highlighter;
use Psy\Exception\RuntimeException;
class CodeFormatter implements Formatter
{
    public static function format(\Reflector $reflector)
    {
        if ($fileName = $reflector->getFileName()) {
            if (!is_file($fileName)) {
                throw new RuntimeException('Source code unavailable.');
            }
            $file  = file_get_contents($fileName);
            $start = $reflector->getStartLine();
            $end   = $reflector->getEndLine() - $start;
            $colors = new ConsoleColor();
            $colors->addTheme('line_number', array('blue'));
            $highlighter = new Highlighter($colors);
            return $highlighter->getCodeSnippet($file, $start, 0, $end);
            return implode(PHP_EOL, $code);
        } else {
            throw new RuntimeException('Source code unavailable.');
        }
    }
}
