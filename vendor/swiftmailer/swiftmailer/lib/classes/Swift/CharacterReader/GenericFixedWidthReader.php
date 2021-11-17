<?php
class Swift_CharacterReader_GenericFixedWidthReader implements Swift_CharacterReader
{
    private $_width;
    public function __construct($width)
    {
        $this->_width = $width;
    }
    public function getCharPositions($string, $startOffset, &$currentMap, &$ignoredChars)
    {
        $strlen = strlen($string);
        $ignored = $strlen % $this->_width;
        $ignoredChars = substr($string, - $ignored);
        $currentMap = $this->_width;
        return ($strlen - $ignored) / $this->_width;
    }
    public function getMapType()
    {
        return self::MAP_TYPE_FIXED_LEN;
    }
    public function validateByteSequence($bytes, $size)
    {
        $needed = $this->_width - $size;
        return ($needed > -1) ? $needed : -1;
    }
    public function getInitialByteSize()
    {
        return $this->_width;
    }
}
