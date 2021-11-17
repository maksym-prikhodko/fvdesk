<?php
class Font_Glyph_Outline extends Font_Binary_Stream {
  protected $table;
  protected $offset;
  protected $size;
  public $numberOfContours;
  public $xMin;
  public $yMin;
  public $xMax;
  public $yMax;
  public $raw;
  static function init(Font_Table_glyf $table, $offset, $size) {
    $font = $table->getFont();
    $font->seek($offset);
    if ($font->readInt16() > -1) {
      $glyph = new Font_Glyph_Outline_Simple($table, $offset, $size);
    }
    else {
      $glyph = new Font_Glyph_Outline_Composite($table, $offset, $size);
    }
    $glyph->parse();
    return $glyph;
  }
  function getFont() {
    return $this->table->getFont();
  }
  function __construct(Font_Table_glyf $table, $offset = null, $size = null) {
    $this->table  = $table;
    $this->offset = $offset;
    $this->size   = $size;
  }
  function parse() {
    $font = $this->getFont();
    $font->seek($this->offset);
    if (!$this->size) {
      return;
    }
    $this->raw = $font->read($this->size);
  }
  function parseData(){
    $font = $this->getFont();
    $font->seek($this->offset);
    $this->numberOfContours = $font->readInt16();
    $this->xMin = $font->readFWord();
    $this->yMin = $font->readFWord();
    $this->xMax = $font->readFWord();
    $this->yMax = $font->readFWord();
  }
  function encode(){
    $font = $this->getFont();
    return $font->write($this->raw, strlen($this->raw));
  }
  function getSVGContours() {
  }
  function getGlyphIDs(){
    return array();
  }
}
require_once dirname(__FILE__) . "/Font_Glyph_Outline_Simple.php";
require_once dirname(__FILE__) . "/Font_Glyph_Outline_Composite.php";
