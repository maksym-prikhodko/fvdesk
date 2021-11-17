<?php
require_once dirname(__FILE__) . "/Font_Table_Directory_Entry.php";
class Font_WOFF_Table_Directory_Entry extends Font_Table_Directory_Entry {
  public $origLength;
  function __construct(Font_WOFF $font) {
    parent::__construct($font);
  }
  function parse(){
    parent::parse();
    $font = $this->font;
    $this->offset     = $font->readUInt32();
    $this->length     = $font->readUInt32();
    $this->origLength = $font->readUInt32();
    $this->checksum   = $font->readUInt32();
  }
}
