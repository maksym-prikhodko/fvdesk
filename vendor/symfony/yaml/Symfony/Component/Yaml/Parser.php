<?php
namespace Symfony\Component\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
class Parser
{
    const FOLDED_SCALAR_PATTERN = '(?P<separator>\||>)(?P<modifiers>\+|\-|\d+|\+\d+|\-\d+|\d+\+|\d+\-)?(?P<comments> +#.*)?';
    private $offset = 0;
    private $lines = array();
    private $currentLineNb = -1;
    private $currentLine = '';
    private $refs = array();
    public function __construct($offset = 0)
    {
        $this->offset = $offset;
    }
    public function parse($value, $exceptionOnInvalidType = false, $objectSupport = false, $objectForMap = false)
    {
        if (!preg_match('
            throw new ParseException('The YAML value does not appear to be valid UTF-8.');
        }
        $this->currentLineNb = -1;
        $this->currentLine = '';
        $value = $this->cleanup($value);
        $this->lines = explode("\n", $value);
        if (function_exists('mb_internal_encoding') && ((int) ini_get('mbstring.func_overload')) & 2) {
            $mbEncoding = mb_internal_encoding();
            mb_internal_encoding('UTF-8');
        }
        $data = array();
        $context = null;
        $allowOverwrite = false;
        while ($this->moveToNextLine()) {
            if ($this->isCurrentLineEmpty()) {
                continue;
            }
            if ("\t" === $this->currentLine[0]) {
                throw new ParseException('A YAML file cannot contain tabs as indentation.', $this->getRealCurrentLineNb() + 1, $this->currentLine);
            }
            $isRef = $mergeNode = false;
            if (preg_match('#^\-((?P<leadspaces>\s+)(?P<value>.+?))?\s*$#u', $this->currentLine, $values)) {
                if ($context && 'mapping' == $context) {
                    throw new ParseException('You cannot define a sequence item when in a mapping');
                }
                $context = 'sequence';
                if (isset($values['value']) && preg_match('#^&(?P<ref>[^ ]+) *(?P<value>.*)#u', $values['value'], $matches)) {
                    $isRef = $matches['ref'];
                    $values['value'] = $matches['value'];
                }
                if (!isset($values['value']) || '' == trim($values['value'], ' ') || 0 === strpos(ltrim($values['value'], ' '), '#')) {
                    $c = $this->getRealCurrentLineNb() + 1;
                    $parser = new Parser($c);
                    $parser->refs = &$this->refs;
                    $data[] = $parser->parse($this->getNextEmbedBlock(null, true), $exceptionOnInvalidType, $objectSupport, $objectForMap);
                } else {
                    if (isset($values['leadspaces'])
                        && preg_match('#^(?P<key>'.Inline::REGEX_QUOTED_STRING.'|[^ \'"\{\[].*?) *\:(\s+(?P<value>.+?))?\s*$#u', $values['value'], $matches)
                    ) {
                        $c = $this->getRealCurrentLineNb();
                        $parser = new Parser($c);
                        $parser->refs = &$this->refs;
                        $block = $values['value'];
                        if ($this->isNextLineIndented()) {
                            $block .= "\n".$this->getNextEmbedBlock($this->getCurrentLineIndentation() + strlen($values['leadspaces']) + 1);
                        }
                        $data[] = $parser->parse($block, $exceptionOnInvalidType, $objectSupport, $objectForMap);
                    } else {
                        $data[] = $this->parseValue($values['value'], $exceptionOnInvalidType, $objectSupport, $objectForMap);
                    }
                }
            } elseif (preg_match('#^(?P<key>'.Inline::REGEX_QUOTED_STRING.'|[^ \'"\[\{].*?) *\:(\s+(?P<value>.+?))?\s*$#u', $this->currentLine, $values) && (false === strpos($values['key'], ' #') || in_array($values['key'][0], array('"', "'")))) {
                if ($context && 'sequence' == $context) {
                    throw new ParseException('You cannot define a mapping item when in a sequence');
                }
                $context = 'mapping';
                Inline::parse(null, $exceptionOnInvalidType, $objectSupport, $objectForMap, $this->refs);
                try {
                    $key = Inline::parseScalar($values['key']);
                } catch (ParseException $e) {
                    $e->setParsedLine($this->getRealCurrentLineNb() + 1);
                    $e->setSnippet($this->currentLine);
                    throw $e;
                }
                if ('<<' === $key) {
                    $mergeNode = true;
                    $allowOverwrite = true;
                    if (isset($values['value']) && 0 === strpos($values['value'], '*')) {
                        $refName = substr($values['value'], 1);
                        if (!array_key_exists($refName, $this->refs)) {
                            throw new ParseException(sprintf('Reference "%s" does not exist.', $refName), $this->getRealCurrentLineNb() + 1, $this->currentLine);
                        }
                        $refValue = $this->refs[$refName];
                        if (!is_array($refValue)) {
                            throw new ParseException('YAML merge keys used with a scalar value instead of an array.', $this->getRealCurrentLineNb() + 1, $this->currentLine);
                        }
                        foreach ($refValue as $key => $value) {
                            if (!isset($data[$key])) {
                                $data[$key] = $value;
                            }
                        }
                    } else {
                        if (isset($values['value']) && $values['value'] !== '') {
                            $value = $values['value'];
                        } else {
                            $value = $this->getNextEmbedBlock();
                        }
                        $c = $this->getRealCurrentLineNb() + 1;
                        $parser = new Parser($c);
                        $parser->refs = &$this->refs;
                        $parsed = $parser->parse($value, $exceptionOnInvalidType, $objectSupport, $objectForMap);
                        if (!is_array($parsed)) {
                            throw new ParseException('YAML merge keys used with a scalar value instead of an array.', $this->getRealCurrentLineNb() + 1, $this->currentLine);
                        }
                        if (isset($parsed[0])) {
                            foreach ($parsed as $parsedItem) {
                                if (!is_array($parsedItem)) {
                                    throw new ParseException('Merge items must be arrays.', $this->getRealCurrentLineNb() + 1, $parsedItem);
                                }
                                foreach ($parsedItem as $key => $value) {
                                    if (!isset($data[$key])) {
                                        $data[$key] = $value;
                                    }
                                }
                            }
                        } else {
                            foreach ($parsed as $key => $value) {
                                if (!isset($data[$key])) {
                                    $data[$key] = $value;
                                }
                            }
                        }
                    }
                } elseif (isset($values['value']) && preg_match('#^&(?P<ref>[^ ]+) *(?P<value>.*)#u', $values['value'], $matches)) {
                    $isRef = $matches['ref'];
                    $values['value'] = $matches['value'];
                }
                if ($mergeNode) {
                } elseif (!isset($values['value']) || '' == trim($values['value'], ' ') || 0 === strpos(ltrim($values['value'], ' '), '#')) {
                    if (!$this->isNextLineIndented() && !$this->isNextLineUnIndentedCollection()) {
                        if ($allowOverwrite || !isset($data[$key])) {
                            $data[$key] = null;
                        }
                    } else {
                        $c = $this->getRealCurrentLineNb() + 1;
                        $parser = new Parser($c);
                        $parser->refs = &$this->refs;
                        $value = $parser->parse($this->getNextEmbedBlock(), $exceptionOnInvalidType, $objectSupport, $objectForMap);
                        if ($allowOverwrite || !isset($data[$key])) {
                            $data[$key] = $value;
                        }
                    }
                } else {
                    $value = $this->parseValue($values['value'], $exceptionOnInvalidType, $objectSupport, $objectForMap);
                    if ($allowOverwrite || !isset($data[$key])) {
                        $data[$key] = $value;
                    }
                }
            } else {
                if ('---' === $this->currentLine) {
                    throw new ParseException('Multiple documents are not supported.');
                }
                if ($this->lines[0] === trim($value)) {
                    try {
                        $value = Inline::parse($this->lines[0], $exceptionOnInvalidType, $objectSupport, $objectForMap, $this->refs);
                    } catch (ParseException $e) {
                        $e->setParsedLine($this->getRealCurrentLineNb() + 1);
                        $e->setSnippet($this->currentLine);
                        throw $e;
                    }
                    if (is_array($value)) {
                        $first = reset($value);
                        if (is_string($first) && 0 === strpos($first, '*')) {
                            $data = array();
                            foreach ($value as $alias) {
                                $data[] = $this->refs[substr($alias, 1)];
                            }
                            $value = $data;
                        }
                    }
                    if (isset($mbEncoding)) {
                        mb_internal_encoding($mbEncoding);
                    }
                    return $value;
                }
                switch (preg_last_error()) {
                    case PREG_INTERNAL_ERROR:
                        $error = 'Internal PCRE error.';
                        break;
                    case PREG_BACKTRACK_LIMIT_ERROR:
                        $error = 'pcre.backtrack_limit reached.';
                        break;
                    case PREG_RECURSION_LIMIT_ERROR:
                        $error = 'pcre.recursion_limit reached.';
                        break;
                    case PREG_BAD_UTF8_ERROR:
                        $error = 'Malformed UTF-8 data.';
                        break;
                    case PREG_BAD_UTF8_OFFSET_ERROR:
                        $error = 'Offset doesn\'t correspond to the begin of a valid UTF-8 code point.';
                        break;
                    default:
                        $error = 'Unable to parse.';
                }
                throw new ParseException($error, $this->getRealCurrentLineNb() + 1, $this->currentLine);
            }
            if ($isRef) {
                $this->refs[$isRef] = end($data);
            }
        }
        if (isset($mbEncoding)) {
            mb_internal_encoding($mbEncoding);
        }
        return empty($data) ? null : $data;
    }
    private function getRealCurrentLineNb()
    {
        return $this->currentLineNb + $this->offset;
    }
    private function getCurrentLineIndentation()
    {
        return strlen($this->currentLine) - strlen(ltrim($this->currentLine, ' '));
    }
    private function getNextEmbedBlock($indentation = null, $inSequence = false)
    {
        $oldLineIndentation = $this->getCurrentLineIndentation();
        if (!$this->moveToNextLine()) {
            return;
        }
        if (null === $indentation) {
            $newIndent = $this->getCurrentLineIndentation();
            $unindentedEmbedBlock = $this->isStringUnIndentedCollectionItem($this->currentLine);
            if (!$this->isCurrentLineEmpty() && 0 === $newIndent && !$unindentedEmbedBlock) {
                throw new ParseException('Indentation problem.', $this->getRealCurrentLineNb() + 1, $this->currentLine);
            }
        } else {
            $newIndent = $indentation;
        }
        $data = array();
        if ($this->getCurrentLineIndentation() >= $newIndent) {
            $data[] = substr($this->currentLine, $newIndent);
        } else {
            $this->moveToPreviousLine();
            return;
        }
        if ($inSequence && $oldLineIndentation === $newIndent && '-' === $data[0][0]) {
            $this->moveToPreviousLine();
            return;
        }
        $isItUnindentedCollection = $this->isStringUnIndentedCollectionItem($this->currentLine);
        $removeCommentsPattern = '~'.self::FOLDED_SCALAR_PATTERN.'$~';
        $removeComments = !preg_match($removeCommentsPattern, $this->currentLine);
        while ($this->moveToNextLine()) {
            $indent = $this->getCurrentLineIndentation();
            if ($indent === $newIndent) {
                $removeComments = !preg_match($removeCommentsPattern, $this->currentLine);
            }
            if ($isItUnindentedCollection && !$this->isStringUnIndentedCollectionItem($this->currentLine) && $newIndent === $indent) {
                $this->moveToPreviousLine();
                break;
            }
            if ($this->isCurrentLineBlank()) {
                $data[] = substr($this->currentLine, $newIndent);
                continue;
            }
            if ($removeComments && $this->isCurrentLineComment()) {
                continue;
            }
            if ($indent >= $newIndent) {
                $data[] = substr($this->currentLine, $newIndent);
            } elseif (0 == $indent) {
                $this->moveToPreviousLine();
                break;
            } else {
                throw new ParseException('Indentation problem.', $this->getRealCurrentLineNb() + 1, $this->currentLine);
            }
        }
        return implode("\n", $data);
    }
    private function moveToNextLine()
    {
        if ($this->currentLineNb >= count($this->lines) - 1) {
            return false;
        }
        $this->currentLine = $this->lines[++$this->currentLineNb];
        return true;
    }
    private function moveToPreviousLine()
    {
        $this->currentLine = $this->lines[--$this->currentLineNb];
    }
    private function parseValue($value, $exceptionOnInvalidType, $objectSupport, $objectForMap)
    {
        if (0 === strpos($value, '*')) {
            if (false !== $pos = strpos($value, '#')) {
                $value = substr($value, 1, $pos - 2);
            } else {
                $value = substr($value, 1);
            }
            if (!array_key_exists($value, $this->refs)) {
                throw new ParseException(sprintf('Reference "%s" does not exist.', $value), $this->currentLine);
            }
            return $this->refs[$value];
        }
        if (preg_match('/^'.self::FOLDED_SCALAR_PATTERN.'$/', $value, $matches)) {
            $modifiers = isset($matches['modifiers']) ? $matches['modifiers'] : '';
            return $this->parseFoldedScalar($matches['separator'], preg_replace('#\d+#', '', $modifiers), (int) abs($modifiers));
        }
        try {
            return Inline::parse($value, $exceptionOnInvalidType, $objectSupport, $objectForMap, $this->refs);
        } catch (ParseException $e) {
            $e->setParsedLine($this->getRealCurrentLineNb() + 1);
            $e->setSnippet($this->currentLine);
            throw $e;
        }
    }
    private function parseFoldedScalar($separator, $indicator = '', $indentation = 0)
    {
        $notEOF = $this->moveToNextLine();
        if (!$notEOF) {
            return '';
        }
        $isCurrentLineBlank = $this->isCurrentLineBlank();
        $text = '';
        while ($notEOF && $isCurrentLineBlank) {
            if ($notEOF = $this->moveToNextLine()) {
                $text .= "\n";
                $isCurrentLineBlank = $this->isCurrentLineBlank();
            }
        }
        if (0 === $indentation) {
            if (preg_match('/^ +/', $this->currentLine, $matches)) {
                $indentation = strlen($matches[0]);
            }
        }
        if ($indentation > 0) {
            $pattern = sprintf('/^ {%d}(.*)$/', $indentation);
            while (
                $notEOF && (
                    $isCurrentLineBlank ||
                    preg_match($pattern, $this->currentLine, $matches)
                )
            ) {
                if ($isCurrentLineBlank) {
                    $text .= substr($this->currentLine, $indentation);
                } else {
                    $text .= $matches[1];
                }
                if ($notEOF = $this->moveToNextLine()) {
                    $text .= "\n";
                    $isCurrentLineBlank = $this->isCurrentLineBlank();
                }
            }
        } elseif ($notEOF) {
            $text .= "\n";
        }
        if ($notEOF) {
            $this->moveToPreviousLine();
        }
        if ('>' === $separator) {
            preg_match('/(\n*)$/', $text, $matches);
            $text = preg_replace('/(?<!\n)\n(?!\n)/', ' ', rtrim($text, "\n"));
            $text .= $matches[1];
        }
        if ('' === $indicator) {
            $text = preg_replace('/\n+$/s', "\n", $text);
        } elseif ('-' === $indicator) {
            $text = preg_replace('/\n+$/s', '', $text);
        }
        return $text;
    }
    private function isNextLineIndented()
    {
        $currentIndentation = $this->getCurrentLineIndentation();
        $EOF = !$this->moveToNextLine();
        while (!$EOF && $this->isCurrentLineEmpty()) {
            $EOF = !$this->moveToNextLine();
        }
        if ($EOF) {
            return false;
        }
        $ret = false;
        if ($this->getCurrentLineIndentation() > $currentIndentation) {
            $ret = true;
        }
        $this->moveToPreviousLine();
        return $ret;
    }
    private function isCurrentLineEmpty()
    {
        return $this->isCurrentLineBlank() || $this->isCurrentLineComment();
    }
    private function isCurrentLineBlank()
    {
        return '' == trim($this->currentLine, ' ');
    }
    private function isCurrentLineComment()
    {
        $ltrimmedLine = ltrim($this->currentLine, ' ');
        return $ltrimmedLine[0] === '#';
    }
    private function cleanup($value)
    {
        $value = str_replace(array("\r\n", "\r"), "\n", $value);
        $count = 0;
        $value = preg_replace('#^\%YAML[: ][\d\.]+.*\n#u', '', $value, -1, $count);
        $this->offset += $count;
        $trimmedValue = preg_replace('#^(\#.*?\n)+#s', '', $value, -1, $count);
        if ($count == 1) {
            $this->offset += substr_count($value, "\n") - substr_count($trimmedValue, "\n");
            $value = $trimmedValue;
        }
        $trimmedValue = preg_replace('#^\-\-\-.*?\n#s', '', $value, -1, $count);
        if ($count == 1) {
            $this->offset += substr_count($value, "\n") - substr_count($trimmedValue, "\n");
            $value = $trimmedValue;
            $value = preg_replace('#\.\.\.\s*$#s', '', $value);
        }
        return $value;
    }
    private function isNextLineUnIndentedCollection()
    {
        $currentIndentation = $this->getCurrentLineIndentation();
        $notEOF = $this->moveToNextLine();
        while ($notEOF && $this->isCurrentLineEmpty()) {
            $notEOF = $this->moveToNextLine();
        }
        if (false === $notEOF) {
            return false;
        }
        $ret = false;
        if (
            $this->getCurrentLineIndentation() == $currentIndentation
            &&
            $this->isStringUnIndentedCollectionItem($this->currentLine)
        ) {
            $ret = true;
        }
        $this->moveToPreviousLine();
        return $ret;
    }
    private function isStringUnIndentedCollectionItem()
    {
        return (0 === strpos($this->currentLine, '- '));
    }
}
