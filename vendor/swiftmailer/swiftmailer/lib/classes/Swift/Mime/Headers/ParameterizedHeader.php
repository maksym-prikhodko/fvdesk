<?php
class Swift_Mime_Headers_ParameterizedHeader extends Swift_Mime_Headers_UnstructuredHeader implements Swift_Mime_ParameterizedHeader
{
    const TOKEN_REGEX = '(?:[\x21\x23-\x27\x2A\x2B\x2D\x2E\x30-\x39\x41-\x5A\x5E-\x7E]+)';
    private $_paramEncoder;
    private $_params = array();
    public function __construct($name, Swift_Mime_HeaderEncoder $encoder, Swift_Encoder $paramEncoder = null, Swift_Mime_Grammar $grammar)
    {
        parent::__construct($name, $encoder, $grammar);
        $this->_paramEncoder = $paramEncoder;
    }
    public function getFieldType()
    {
        return self::TYPE_PARAMETERIZED;
    }
    public function setCharset($charset)
    {
        parent::setCharset($charset);
        if (isset($this->_paramEncoder)) {
            $this->_paramEncoder->charsetChanged($charset);
        }
    }
    public function setParameter($parameter, $value)
    {
        $this->setParameters(array_merge($this->getParameters(), array($parameter => $value)));
    }
    public function getParameter($parameter)
    {
        $params = $this->getParameters();
        return array_key_exists($parameter, $params)
            ? $params[$parameter]
            : null;
    }
    public function setParameters(array $parameters)
    {
        $this->clearCachedValueIf($this->_params != $parameters);
        $this->_params = $parameters;
    }
    public function getParameters()
    {
        return $this->_params;
    }
    public function getFieldBody() 
    {
        $body = parent::getFieldBody();
        foreach ($this->_params as $name => $value) {
            if (!is_null($value)) {
                $body .= '; '.$this->_createParameter($name, $value);
            }
        }
        return $body;
    }
    protected function toTokens($string = null)
    {
        $tokens = parent::toTokens(parent::getFieldBody());
        foreach ($this->_params as $name => $value) {
            if (!is_null($value)) {
                $tokens[count($tokens)-1] .= ';';
                $tokens = array_merge($tokens, $this->generateTokenLines(
                    ' '.$this->_createParameter($name, $value)
                    ));
            }
        }
        return $tokens;
    }
    private function _createParameter($name, $value)
    {
        $origValue = $value;
        $encoded = false;
        $maxValueLength = $this->getMaxLineLength() - strlen($name.'=*N"";') - 1;
        $firstLineOffset = 0;
        if (!preg_match('/^'.self::TOKEN_REGEX.'$/D', $value)) {
            if (!preg_match('/^'.$this->getGrammar()->getDefinition('text').'*$/D', $value)) {
                $encoded = true;
                $maxValueLength = $this->getMaxLineLength() - strlen($name.'*N*="";') - 1;
                $firstLineOffset = strlen(
                    $this->getCharset()."'".$this->getLanguage()."'"
                    );
            }
        }
        if ($encoded || strlen($value) > $maxValueLength) {
            if (isset($this->_paramEncoder)) {
                $value = $this->_paramEncoder->encodeString(
                    $origValue, $firstLineOffset, $maxValueLength, $this->getCharset()
                    );
            } else {
                $value = $this->getTokenAsEncodedWord($origValue);
                $encoded = false;
            }
        }
        $valueLines = isset($this->_paramEncoder) ? explode("\r\n", $value) : array($value);
        if (count($valueLines) > 1) {
            $paramLines = array();
            foreach ($valueLines as $i => $line) {
                $paramLines[] = $name.'*'.$i.
                    $this->_getEndOfParameterValue($line, true, $i == 0);
            }
            return implode(";\r\n ", $paramLines);
        } else {
            return $name.$this->_getEndOfParameterValue(
                $valueLines[0], $encoded, true
                );
        }
    }
    private function _getEndOfParameterValue($value, $encoded = false, $firstLine = false)
    {
        if (!preg_match('/^'.self::TOKEN_REGEX.'$/D', $value)) {
            $value = '"'.$value.'"';
        }
        $prepend = '=';
        if ($encoded) {
            $prepend = '*=';
            if ($firstLine) {
                $prepend = '*='.$this->getCharset()."'".$this->getLanguage().
                    "'";
            }
        }
        return $prepend.$value;
    }
}
