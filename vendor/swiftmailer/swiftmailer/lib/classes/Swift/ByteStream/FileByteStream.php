<?php
class Swift_ByteStream_FileByteStream extends Swift_ByteStream_AbstractFilterableInputStream implements Swift_FileStream
{
    private $_offset = 0;
    private $_path;
    private $_mode;
    private $_reader;
    private $_writer;
    private $_quotes = false;
    private $_seekable = null;
    public function __construct($path, $writable = false)
    {
        if (empty($path)) {
            throw new Swift_IoException('The path cannot be empty');
        }
        $this->_path = $path;
        $this->_mode = $writable ? 'w+b' : 'rb';
        if (function_exists('get_magic_quotes_runtime') && @get_magic_quotes_runtime() == 1) {
            $this->_quotes = true;
        }
    }
    public function getPath()
    {
        return $this->_path;
    }
    public function read($length)
    {
        $fp = $this->_getReadHandle();
        if (!feof($fp)) {
            if ($this->_quotes) {
                ini_set('magic_quotes_runtime', 0);
            }
            $bytes = fread($fp, $length);
            if ($this->_quotes) {
                ini_set('magic_quotes_runtime', 1);
            }
            $this->_offset = ftell($fp);
            if ($bytes === '' && feof($fp)) {
                $this->_resetReadHandle();
                return false;
            }
            return $bytes;
        }
        $this->_resetReadHandle();
        return false;
    }
    public function setReadPointer($byteOffset)
    {
        if (isset($this->_reader)) {
            $this->_seekReadStreamToPosition($byteOffset);
        }
        $this->_offset = $byteOffset;
    }
    protected function _commit($bytes)
    {
        fwrite($this->_getWriteHandle(), $bytes);
        $this->_resetReadHandle();
    }
    protected function _flush()
    {
    }
    private function _getReadHandle()
    {
        if (!isset($this->_reader)) {
            if (!$this->_reader = fopen($this->_path, 'rb')) {
                throw new Swift_IoException(
                    'Unable to open file for reading ['.$this->_path.']'
                );
            }
            if ($this->_offset != 0) {
                $this->_getReadStreamSeekableStatus();
                $this->_seekReadStreamToPosition($this->_offset);
            }
        }
        return $this->_reader;
    }
    private function _getWriteHandle()
    {
        if (!isset($this->_writer)) {
            if (!$this->_writer = fopen($this->_path, $this->_mode)) {
                throw new Swift_IoException(
                    'Unable to open file for writing ['.$this->_path.']'
                );
            }
        }
        return $this->_writer;
    }
    private function _resetReadHandle()
    {
        if (isset($this->_reader)) {
            fclose($this->_reader);
            $this->_reader = null;
        }
    }
    private function _getReadStreamSeekableStatus()
    {
        $metas = stream_get_meta_data($this->_reader);
        $this->_seekable = $metas['seekable'];
    }
    private function _seekReadStreamToPosition($offset)
    {
        if ($this->_seekable === null) {
            $this->_getReadStreamSeekableStatus();
        }
        if ($this->_seekable === false) {
            $currentPos = ftell($this->_reader);
            if ($currentPos<$offset) {
                $toDiscard = $offset-$currentPos;
                fread($this->_reader, $toDiscard);
                return;
            }
            $this->_copyReadStream();
        }
        fseek($this->_reader, $offset, SEEK_SET);
    }
    private function _copyReadStream()
    {
        if ($tmpFile = fopen('php:
        } elseif (function_exists('sys_get_temp_dir') && is_writable(sys_get_temp_dir()) && ($tmpFile = tmpfile())) {
        } else {
            throw new Swift_IoException('Unable to copy the file to make it seekable, sys_temp_dir is not writable, php:
        }
        $currentPos = ftell($this->_reader);
        fclose($this->_reader);
        $source = fopen($this->_path, 'rb');
        if (!$source) {
            throw new Swift_IoException('Unable to open file for copying ['.$this->_path.']');
        }
        fseek($tmpFile, 0, SEEK_SET);
        while (!feof($source)) {
            fwrite($tmpFile, fread($source, 4096));
        }
        fseek($tmpFile, $currentPos, SEEK_SET);
        fclose($source);
        $this->_reader = $tmpFile;
    }
}
