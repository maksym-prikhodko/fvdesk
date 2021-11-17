<?php
require_once DOMPDF_LIB_DIR . '/tcpdf/tcpdf.php';
class TCPDF_Adapter implements Canvas {
  static public $PAPER_SIZES = array(); 
  private $_dompdf;
  private $_pdf;
  private $_width;
  private $_height;
  private $_last_fill_color;
  private $_last_stroke_color;
  private $_last_line_width;
  private $_page_count;
  private $_page_text;
  private $_pages;
  function __construct($paper = "letter", $orientation = "portrait", DOMPDF $dompdf) {
    if ( is_array($paper) )
      $size = $paper;
    else if ( isset(self::$PAPER_SIZES[mb_strtolower($paper)]) )
      $size = self::$PAPER_SIZES[$paper];
    else
      $size = self::$PAPER_SIZES["letter"];
    if ( mb_strtolower($orientation) === "landscape" ) {
      list($size[2], $size[3]) = array($size[3], $size[2]);
    }
    $this->_width  = $size[2] - $size[0];
    $this->_height = $size[3] - $size[1];
    $this->_dompdf = $dompdf;
    $this->_pdf = new TCPDF("P", "pt", array($this->_width, $this->_height));
    $this->_pdf->Setcreator("DOMPDF Converter");
    $this->_pdf->AddPage();
    $this->_page_number = $this->_page_count = 1;
    $this->_page_text = array();
    $this->_last_fill_color   = null;
    $this->_last_stroke_color = null;
    $this->_last_line_width   = null;
  }
  function get_dompdf(){
    return $this->_dompdf;
  }
  protected function y($y) { return $this->_height - $y; }
  protected function _set_stroke_color($color) {
    $color[0] = round(255 * $color[0]);
    $color[1] = round(255 * $color[1]);
    $color[2] = round(255 * $color[2]);
    if ( is_null($this->_last_stroke_color) || $color != $this->_last_stroke_color ) {
      $this->_pdf->SetDrawColor($color[0],$color[1],$color[2]);
      $this->_last_stroke_color = $color;
    }
  }
  protected function _set_fill_color($color) {
    $color[0] = round(255 * $color[0]);
    $color[1] = round(255 * $color[1]);
    $color[2] = round(255 * $color[2]);
    if ( is_null($this->_last_fill_color) || $color != $this->_last_fill_color ) {
      $this->_pdf->SetDrawColor($color[0],$color[1],$color[2]);
      $this->_last_fill_color = $color;
    }
  }
  function get_tcpdf() { return $this->_pdf; }
  function get_page_number() {
    return $this->_page_number;
  }
  function get_page_count() {
    return $this->_page_count;
  }
  function set_page_count($count) {
    $this->_page_count = (int)$count;
  }
  function line($x1, $y1, $x2, $y2, $color, $width, $style = null) {
    if ( is_null($this->_last_line_width) || $width != $this->_last_line_width ) {
      $this->_pdf->SetLineWidth($width);
      $this->_last_line_width = $width;
    }
    $this->_set_stroke_color($color);
    $this->_pdf->line($x1, $y1, $x2, $y2);
  }
  function rectangle($x1, $y1, $w, $h, $color, $width, $style = null) {
    if ( is_null($this->_last_line_width) || $width != $this->_last_line_width ) {
      $this->_pdf->SetLineWidth($width);
      $this->_last_line_width = $width;
    }
    $this->_set_stroke_color($color);
    $this->_pdf->rect($x1, $y1, $w, $h);
  }
  function filled_rectangle($x1, $y1, $w, $h, $color) {
    $this->_set_fill_color($color);
    $this->_pdf->rect($x1, $y1, $w, $h, "F");
  }
  function polygon($points, $color, $width = null, $style = null, $fill = false) {
  }
  function circle($x, $y, $r, $color, $width = null, $style = null, $fill = false) {
  }
  function image($img_url, $x, $y, $w, $h, $resolution = "normal") {
  }
  function text($x, $y, $text, $font, $size, $color = array(0,0,0), $word_space = 0.0, $char_space = 0.0, $angle = 0.0) {
  }
  function javascript($code) {
  }
  function add_named_dest($anchorname) {
  }
  function add_link($url, $x, $y, $width, $height) {
  }
  function add_info($label, $value) {
    $method = "Set$label";
    if ( in_array("Title", "Author", "Keywords", "Subject") && method_exists($this->_pdf, $method) ) {
      $this->_pdf->$method($value);
    }
  }
  function get_text_width($text, $font, $size, $word_spacing = 0.0, $char_spacing = 0.0) {
  }
  function get_font_height($font, $size) {
  }
  function new_page() {
  }
  function stream($filename, $options = null) {
  }
  function output($options = null) {
  }
  function clipping_rectangle($x1, $y1, $w, $h) {
  }
  function clipping_roundrectangle($x1, $y1, $w, $h, $tl, $tr, $br, $bl) {
  }
  function clipping_end() {
  }
  function save() {
  }
  function restore() {
  }
  function rotate($angle, $x, $y) {
  }
  function skew($angle_x, $angle_y, $x, $y) {
  }
  function scale($s_x, $s_y, $x, $y) {
  }
  function translate($t_x, $t_y) {
  }
  function transform($a, $b, $c, $d, $e, $f) {
  }
  function arc($x, $y, $r1, $r2, $astart, $aend, $color, $width, $style = array()) {
  }
  function get_font_baseline($font, $size) {
  }
  function set_opacity($opacity, $mode = "Normal") {
  }
  function set_default_view($view, $options = array()) {
  }}
TCPDF_Adapter::$PAPER_SIZES = CPDF_Adapter::$PAPER_SIZES;
