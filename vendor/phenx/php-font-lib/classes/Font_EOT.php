<?php
require_once dirname(__FILE__) . "/Font_TrueType.php";
require_once dirname(__FILE__) . "/Font_EOT_Header.php";
class Font_EOT extends Font_TrueType {
  const TTEMBED_SUBSET                   = 0x00000001;
  const TTEMBED_TTCOMPRESSED             = 0x00000004;
  const TTEMBED_FAILIFVARIATIONSIMULATED = 0x00000010;
  const TTMBED_EMBEDEUDC                 = 0x00000020;
  const TTEMBED_VALIDATIONTESTS          = 0x00000040; 
  const TTEMBED_WEBOBJECT                = 0x00000080;
  const TTEMBED_XORENCRYPTDATA           = 0x10000000;
  public $header;
  function parseHeader(){
    if (!empty($this->header)) {
      return;
    }
    $this->header = new Font_EOT_Header($this);
    $this->header->parse();
  }
  function parse() {
    $this->parseHeader();
    $flags = $this->header->data["Flags"];
    if ($flags & self::TTEMBED_TTCOMPRESSED) {
      $mtx_version    = $this->readUInt8();
      $mtx_copy_limit = $this->readUInt8() << 16 | $this->readUInt8() << 8 | $this->readUInt8();
      $mtx_offset_1   = $this->readUInt8() << 16 | $this->readUInt8() << 8 | $this->readUInt8();
      $mtx_offset_2   = $this->readUInt8() << 16 | $this->readUInt8() << 8 | $this->readUInt8();
    }
    if ($flags & self::TTEMBED_XORENCRYPTDATA) {
    }
  }
  public function read($n) {
    if ($n < 1) {
      return "";
    }
    $string = fread($this->f, $n);
    $chunks = str_split($string, 2);
    $chunks = array_map("strrev", $chunks);
    return implode("", $chunks);
  }
  public function readUInt32(){
    $uint32 = parent::readUInt32();
    return $uint32 >> 16 & 0x0000FFFF | $uint32 << 16 & 0xFFFF0000;
  }
  function getFontCopyright(){
    return null;
  }
  function getFontName(){
    return $this->header->data["FamilyName"];
  }
  function getFontSubfamily(){
    return $this->header->data["StyleName"];
  }
  function getFontSubfamilyID(){
    return $this->header->data["StyleName"];
  }
  function getFontFullName(){
    return $this->header->data["FullName"];
  }
  function getFontVersion(){
    return $this->header->data["VersionName"];
  }
  function getFontWeight(){
    return $this->header->data["Weight"];
  }
  function getFontPostscriptName(){
    return null;
  }
}
