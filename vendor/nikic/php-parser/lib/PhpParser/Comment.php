<?php
namespace PhpParser;
class Comment
{
    protected $text;
    protected $line;
    public function __construct($text, $line = -1) {
        $this->text = $text;
        $this->line = $line;
    }
    public function getText() {
        return $this->text;
    }
    public function setText($text) {
        $this->text = $text;
    }
    public function getLine() {
        return $this->line;
    }
    public function setLine($line) {
        $this->line = $line;
    }
    public function __toString() {
        return $this->text;
    }
    public function getReformattedText() {
        $text = trim($this->text);
        if (false === strpos($text, "\n")) {
            return $text;
        } elseif (preg_match('((*BSR_ANYCRLF)(*ANYCRLF)^.*(?:\R\s+\*.*)+$)', $text)) {
            return preg_replace('(^\s+\*)m', ' *', $this->text);
        } elseif (preg_match('(^/\*\*?\s*[\r\n])', $text) && preg_match('(\n(\s*)\*/$)', $text, $matches)) {
            return preg_replace('(^' . preg_quote($matches[1]) . ')m', '', $text);
        } elseif (preg_match('(^/\*\*?\s*(?!\s))', $text, $matches)) {
            return preg_replace('(^\s*(?= {' . strlen($matches[0]) . '}(?!\s)))m', '', $text);
        }
        return $text;
    }
}
