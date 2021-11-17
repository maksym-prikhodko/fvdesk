<?php
class Swift_Mime_ContentEncoder_QpContentEncoder extends Swift_Encoder_QpEncoder implements Swift_Mime_ContentEncoder
{
    protected $_dotEscape;
    public function __construct(Swift_CharacterStream $charStream, Swift_StreamFilter $filter = null, $dotEscape = false)
    {
        $this->_dotEscape = $dotEscape;
        parent::__construct($charStream, $filter);
    }
    public function __sleep()
    {
        return array('_charStream', '_filter', '_dotEscape');
    }
    protected function getSafeMapShareId()
    {
        return get_class($this).($this->_dotEscape ? '.dotEscape' : '');
    }
    protected function initSafeMap()
    {
        parent::initSafeMap();
        if ($this->_dotEscape) {
            unset($this->_safeMap[0x2e]);
        }
    }
    public function encodeByteStream(Swift_OutputByteStream $os, Swift_InputByteStream $is, $firstLineOffset = 0, $maxLineLength = 0)
    {
        if ($maxLineLength > 76 || $maxLineLength <= 0) {
            $maxLineLength = 76;
        }
        $thisLineLength = $maxLineLength - $firstLineOffset;
        $this->_charStream->flushContents();
        $this->_charStream->importByteStream($os);
        $currentLine = '';
        $prepend = '';
        $size = $lineLen = 0;
        while (false !== $bytes = $this->_nextSequence()) {
            if (isset($this->_filter)) {
                while ($this->_filter->shouldBuffer($bytes)) {
                    if (false === $moreBytes = $this->_nextSequence(1)) {
                        break;
                    }
                    foreach ($moreBytes as $b) {
                        $bytes[] = $b;
                    }
                }
                $bytes = $this->_filter->filter($bytes);
            }
            $enc = $this->_encodeByteSequence($bytes, $size);
            if ($currentLine && $lineLen+$size >= $thisLineLength) {
                $is->write($prepend.$this->_standardize($currentLine));
                $currentLine = '';
                $prepend = "=\r\n";
                $thisLineLength = $maxLineLength;
                $lineLen = 0;
            }
            $lineLen += $size;
            $currentLine .= $enc;
        }
        if (strlen($currentLine)) {
            $is->write($prepend.$this->_standardize($currentLine));
        }
    }
    public function getName()
    {
        return 'quoted-printable';
    }
}
