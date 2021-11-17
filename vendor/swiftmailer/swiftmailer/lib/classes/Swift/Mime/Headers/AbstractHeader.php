<?php
abstract class Swift_Mime_Headers_AbstractHeader implements Swift_Mime_Header
{
    private $_name;
    private $_grammar;
    private $_encoder;
    private $_lineLength = 78;
    private $_lang;
    private $_charset = 'utf-8';
    private $_cachedValue = null;
    public function __construct(Swift_Mime_Grammar $grammar)
    {
        $this->setGrammar($grammar);
    }
    public function setCharset($charset)
    {
        $this->clearCachedValueIf($charset != $this->_charset);
        $this->_charset = $charset;
        if (isset($this->_encoder)) {
            $this->_encoder->charsetChanged($charset);
        }
    }
    public function getCharset()
    {
        return $this->_charset;
    }
    public function setLanguage($lang)
    {
        $this->clearCachedValueIf($this->_lang != $lang);
        $this->_lang = $lang;
    }
    public function getLanguage()
    {
        return $this->_lang;
    }
    public function setEncoder(Swift_Mime_HeaderEncoder $encoder)
    {
        $this->_encoder = $encoder;
        $this->setCachedValue(null);
    }
    public function getEncoder()
    {
        return $this->_encoder;
    }
    public function setGrammar(Swift_Mime_Grammar $grammar)
    {
        $this->_grammar = $grammar;
        $this->setCachedValue(null);
    }
    public function getGrammar()
    {
        return $this->_grammar;
    }
    public function getFieldName()
    {
        return $this->_name;
    }
    public function setMaxLineLength($lineLength)
    {
        $this->clearCachedValueIf($this->_lineLength != $lineLength);
        $this->_lineLength = $lineLength;
    }
    public function getMaxLineLength()
    {
        return $this->_lineLength;
    }
    public function toString()
    {
        return $this->_tokensToString($this->toTokens());
    }
    public function __toString()
    {
        return $this->toString();
    }
    protected function setFieldName($name)
    {
        $this->_name = $name;
    }
    protected function createPhrase(Swift_Mime_Header $header, $string, $charset, Swift_Mime_HeaderEncoder $encoder = null, $shorten = false)
    {
        $phraseStr = $string;
        if (!preg_match('/^'.$this->getGrammar()->getDefinition('phrase').'$/D', $phraseStr)) {
            if (preg_match('/^'.$this->getGrammar()->getDefinition('text').'*$/D', $phraseStr)) {
                $phraseStr = $this->getGrammar()->escapeSpecials(
                    $phraseStr, array('"'), $this->getGrammar()->getSpecials()
                    );
                $phraseStr = '"'.$phraseStr.'"';
            } else {
                if ($shorten) {
                    $usedLength = strlen($header->getFieldName().': ');
                } else {
                    $usedLength = 0;
                }
                $phraseStr = $this->encodeWords($header, $string, $usedLength);
            }
        }
        return $phraseStr;
    }
    protected function encodeWords(Swift_Mime_Header $header, $input, $usedLength = -1)
    {
        $value = '';
        $tokens = $this->getEncodableWordTokens($input);
        foreach ($tokens as $token) {
            if ($this->tokenNeedsEncoding($token)) {
                $firstChar = substr($token, 0, 1);
                switch ($firstChar) {
                    case ' ':
                    case "\t":
                        $value .= $firstChar;
                        $token = substr($token, 1);
                }
                if (-1 == $usedLength) {
                    $usedLength = strlen($header->getFieldName().': ') + strlen($value);
                }
                $value .= $this->getTokenAsEncodedWord($token, $usedLength);
                $header->setMaxLineLength(76); 
            } else {
                $value .= $token;
            }
        }
        return $value;
    }
    protected function tokenNeedsEncoding($token)
    {
        return preg_match('~[\x00-\x08\x10-\x19\x7F-\xFF\r\n]~', $token);
    }
    protected function getEncodableWordTokens($string)
    {
        $tokens = array();
        $encodedToken = '';
        foreach (preg_split('~(?=[\t ])~', $string) as $token) {
            if ($this->tokenNeedsEncoding($token)) {
                $encodedToken .= $token;
            } else {
                if (strlen($encodedToken) > 0) {
                    $tokens[] = $encodedToken;
                    $encodedToken = '';
                }
                $tokens[] = $token;
            }
        }
        if (strlen($encodedToken)) {
            $tokens[] = $encodedToken;
        }
        return $tokens;
    }
    protected function getTokenAsEncodedWord($token, $firstLineOffset = 0)
    {
        $charsetDecl = $this->_charset;
        if (isset($this->_lang)) {
            $charsetDecl .= '*'.$this->_lang;
        }
        $encodingWrapperLength = strlen(
            '=?'.$charsetDecl.'?'.$this->_encoder->getName().'??='
            );
        if ($firstLineOffset >= 75) {
            $firstLineOffset = 0;
        }
        $encodedTextLines = explode("\r\n",
            $this->_encoder->encodeString(
                $token, $firstLineOffset, 75 - $encodingWrapperLength, $this->_charset
                )
        );
        if (strtolower($this->_charset) !== 'iso-2022-jp') {
            foreach ($encodedTextLines as $lineNum => $line) {
                $encodedTextLines[$lineNum] = '=?'.$charsetDecl.
                    '?'.$this->_encoder->getName().
                    '?'.$line.'?=';
            }
        }
        return implode("\r\n ", $encodedTextLines);
    }
    protected function generateTokenLines($token)
    {
        return preg_split('~(\r\n)~', $token, -1, PREG_SPLIT_DELIM_CAPTURE);
    }
    protected function setCachedValue($value)
    {
        $this->_cachedValue = $value;
    }
    protected function getCachedValue()
    {
        return $this->_cachedValue;
    }
    protected function clearCachedValueIf($condition)
    {
        if ($condition) {
            $this->setCachedValue(null);
        }
    }
    protected function toTokens($string = null)
    {
        if (is_null($string)) {
            $string = $this->getFieldBody();
        }
        $tokens = array();
        foreach (preg_split('~(?=[ \t])~', $string) as $token) {
            $newTokens = $this->generateTokenLines($token);
            foreach ($newTokens as $newToken) {
                $tokens[] = $newToken;
            }
        }
        return $tokens;
    }
    private function _tokensToString(array $tokens)
    {
        $lineCount = 0;
        $headerLines = array();
        $headerLines[] = $this->_name.': ';
        $currentLine = & $headerLines[$lineCount++];
        foreach ($tokens as $i => $token) {
            if (("\r\n" == $token) ||
                ($i > 0 && strlen($currentLine.$token) > $this->_lineLength)
                && 0 < strlen($currentLine)) {
                $headerLines[] = '';
                $currentLine = & $headerLines[$lineCount++];
            }
            if ("\r\n" != $token) {
                $currentLine .= $token;
            }
        }
        return implode("\r\n", $headerLines)."\r\n";
    }
}
