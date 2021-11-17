<?php
class PDFLib_Adapter implements Canvas {
  static public $PAPER_SIZES = array(); 
  static $IN_MEMORY = true;
  private $_dompdf;
  private $_pdf;
  private $_file;
  private $_width;
  private $_height;
  private $_last_fill_color;
  private $_last_stroke_color;
  private $_imgs;
  private $_fonts;
  private $_objs;
  private $_page_number;
  private $_page_count;
  private $_page_text;
  private $_pages;
  function __construct($paper = "letter", $orientation = "portrait", DOMPDF $dompdf) {
    if ( is_array($paper) ) {
      $size = $paper;
    }
    else if ( isset(self::$PAPER_SIZES[mb_strtolower($paper)]) ) {
      $size = self::$PAPER_SIZES[mb_strtolower($paper)];
    }
    else {
      $size = self::$PAPER_SIZES["letter"];
    }
    if ( mb_strtolower($orientation) === "landscape" ) {
      list($size[2], $size[3]) = array($size[3], $size[2]);
    }
    $this->_width = $size[2] - $size[0];
    $this->_height= $size[3] - $size[1];
    $this->_dompdf = $dompdf;
    $this->_pdf = new PDFLib();
    if ( defined("DOMPDF_PDFLIB_LICENSE") )
      $this->_pdf->set_parameter( "license", DOMPDF_PDFLIB_LICENSE);
    $this->_pdf->set_parameter("textformat", "utf8");
    $this->_pdf->set_parameter("fontwarning", "false");
    $this->_pdf->set_info("Creator", "DOMPDF");
    $tz = @date_default_timezone_get();
    date_default_timezone_set("UTC");
    $this->_pdf->set_info("Date", date("Y-m-d"));
    date_default_timezone_set($tz);
    if ( self::$IN_MEMORY )
      $this->_pdf->begin_document("","");
    else {
      $tmp_dir = $this->_dompdf->get_options("temp_dir");
      $tmp_name = tempnam($tmp_dir, "libdompdf_pdf_");
      @unlink($tmp_name);
      $this->_file = "$tmp_name.pdf";
      $this->_pdf->begin_document($this->_file,"");
    }
    $this->_pdf->begin_page_ext($this->_width, $this->_height, "");
    $this->_page_number = $this->_page_count = 1;
    $this->_page_text = array();
    $this->_imgs = array();
    $this->_fonts = array();
    $this->_objs = array();
    $families = Font_Metrics::get_font_families();
    foreach ($families as $files) {
      foreach ($files as $file) {
        $face = basename($file);
        $afm = null;
        if ( file_exists("$file.ttf") ) {
          $outline = "$file.ttf";
        } else if ( file_exists("$file.TTF") ) {
          $outline = "$file.TTF";
        } else if ( file_exists("$file.pfb") ) {
          $outline = "$file.pfb";
          if ( file_exists("$file.afm") ) {
            $afm = "$file.afm";
          }
        } else if ( file_exists("$file.PFB") ) {
          $outline = "$file.PFB";
          if ( file_exists("$file.AFM") ) {
            $afm = "$file.AFM";
          }
        } else {
          continue;
        }
        $this->_pdf->set_parameter("FontOutline", "\{$face\}=\{$outline\}");
        if ( !is_null($afm) ) {
          $this->_pdf->set_parameter("FontAFM", "\{$face\}=\{$afm\}");
        }
      }
    }
  }
  function get_dompdf(){
    return $this->_dompdf;
  }
  protected function _close() {
    $this->_place_objects();
    $this->_pdf->suspend_page("");
    for ($p = 1; $p <= $this->_page_count; $p++) {
      $this->_pdf->resume_page("pagenumber=$p");
      $this->_pdf->end_page_ext("");
    }
    $this->_pdf->end_document("");
  }
  function get_pdflib() {
    return $this->_pdf;
  }
  function add_info($label, $value) {
    $this->_pdf->set_info($label, $value);
  }
  function open_object() {
    $this->_pdf->suspend_page("");
    $ret = $this->_pdf->begin_template($this->_width, $this->_height);
    $this->_pdf->save();
    $this->_objs[$ret] = array("start_page" => $this->_page_number);
    return $ret;
  }
  function reopen_object($object) {
    throw new DOMPDF_Exception("PDFLib does not support reopening objects.");
  }
  function close_object() {
    $this->_pdf->restore();
    $this->_pdf->end_template();
    $this->_pdf->resume_page("pagenumber=".$this->_page_number);
  }
  function add_object($object, $where = 'all') {
    if ( mb_strpos($where, "next") !== false ) {
      $this->_objs[$object]["start_page"]++;
      $where = str_replace("next", "", $where);
      if ( $where == "" )
        $where = "add";
    }
    $this->_objs[$object]["where"] = $where;
  }
  function stop_object($object) {
    if ( !isset($this->_objs[$object]) )
      return;
    $start = $this->_objs[$object]["start_page"];
    $where = $this->_objs[$object]["where"];
    if ( $this->_page_number >= $start &&
         (($this->_page_number % 2 == 0 && $where === "even") ||
          ($this->_page_number % 2 == 1 && $where === "odd") ||
          ($where === "all")) ) {
      $this->_pdf->fit_image($object, 0, 0, "");
    }
    $this->_objs[$object] = null;
    unset($this->_objs[$object]);
  }
  protected function _place_objects() {
    foreach ( $this->_objs as $obj => $props ) {
      $start = $props["start_page"];
      $where = $props["where"];
      if ( $this->_page_number >= $start &&
           (($this->_page_number % 2 == 0 && $where === "even") ||
            ($this->_page_number % 2 == 1 && $where === "odd") ||
            ($where === "all")) ) {
        $this->_pdf->fit_image($obj,0,0,"");
      }
    }
  }
  function get_width() { return $this->_width; }
  function get_height() { return $this->_height; }
  function get_page_number() { return $this->_page_number; }
  function get_page_count() { return $this->_page_count; }
  function set_page_number($num) { $this->_page_number = (int)$num; }
  function set_page_count($count) { $this->_page_count = (int)$count; }
  protected function _set_line_style($width, $cap, $join, $dash) {
    if ( count($dash) == 1 )
      $dash[] = $dash[0];
    if ( count($dash) > 1 )
      $this->_pdf->setdashpattern("dasharray={" . implode(" ", $dash) . "}");
    else
      $this->_pdf->setdash(0,0);
    switch ( $join ) {
    case "miter":
      $this->_pdf->setlinejoin(0);
      break;
    case "round":
      $this->_pdf->setlinejoin(1);
      break;
    case "bevel":
      $this->_pdf->setlinejoin(2);
      break;
    default:
      break;
    }
    switch ( $cap ) {
    case "butt":
      $this->_pdf->setlinecap(0);
      break;
    case "round":
      $this->_pdf->setlinecap(1);
      break;
    case "square":
      $this->_pdf->setlinecap(2);
      break;
    default:
      break;
    }
    $this->_pdf->setlinewidth($width);
  }
  protected function _set_stroke_color($color) {
    if($this->_last_stroke_color == $color)
      return;
    $this->_last_stroke_color = $color;
    if (isset($color[3])) {
      $type = "cmyk";
      list($c1, $c2, $c3, $c4) = array($color[0], $color[1], $color[2], $color[3]);
    }
    elseif (isset($color[2])) {
      $type = "rgb";
      list($c1, $c2, $c3, $c4) = array($color[0], $color[1], $color[2], null);
    }
    else {
      $type = "gray";
      list($c1, $c2, $c3, $c4) = array($color[0], $color[1], null, null);
    }
    $this->_pdf->setcolor("stroke", $type, $c1, $c2, $c3, $c4);
  }
  protected function _set_fill_color($color) {
    if($this->_last_fill_color == $color)
      return;
    $this->_last_fill_color = $color;
      if (isset($color[3])) {
      $type = "cmyk";
      list($c1, $c2, $c3, $c4) = array($color[0], $color[1], $color[2], $color[3]);
    }
    elseif (isset($color[2])) {
      $type = "rgb";
      list($c1, $c2, $c3, $c4) = array($color[0], $color[1], $color[2], null);
    }
    else {
      $type = "gray";
      list($c1, $c2, $c3, $c4) = array($color[0], $color[1], null, null);
    }
    $this->_pdf->setcolor("fill", $type, $c1, $c2, $c3, $c4);
  }
  function set_opacity($opacity, $mode = "Normal") {
    if ( $mode === "Normal" ) {
      $gstate = $this->_pdf->create_gstate("opacityfill=$opacity opacitystroke=$opacity");
      $this->_pdf->set_gstate($gstate);
    }
  }
  function set_default_view($view, $options = array()) {
  }
  protected function _load_font($font, $encoding = null, $options = "") {
    $test = strtolower(basename($font));
    if ( in_array($test, DOMPDF::$native_fonts) ) {
      $font = basename($font);
    } else {
      $options .= " embedding=true";
    }
    if ( is_null($encoding) ) {
      if ( defined("DOMPDF_PDFLIB_LICENSE") )
        $encoding = "unicode";
      else
        $encoding = "auto";
    }
    $key = "$font:$encoding:$options";
    if ( isset($this->_fonts[$key]) )
      return $this->_fonts[$key];
    else {
      $this->_fonts[$key] = $this->_pdf->load_font($font, $encoding, $options);
      return $this->_fonts[$key];
    }
  }
  protected function y($y) { return $this->_height - $y; }
  function line($x1, $y1, $x2, $y2, $color, $width, $style = null) {
    $this->_set_line_style($width, "butt", "", $style);
    $this->_set_stroke_color($color);
    $y1 = $this->y($y1);
    $y2 = $this->y($y2);
    $this->_pdf->moveto($x1, $y1);
    $this->_pdf->lineto($x2, $y2);
    $this->_pdf->stroke();
  }
  function arc($x1, $y1, $r1, $r2, $astart, $aend, $color, $width, $style = array()) {
    $this->_set_line_style($width, "butt", "", $style);
    $this->_set_stroke_color($color);
    $y1 = $this->y($y1);
    $this->_pdf->arc($x1, $y1, $r1, $astart, $aend);
    $this->_pdf->stroke();
  }
  function rectangle($x1, $y1, $w, $h, $color, $width, $style = null) {
    $this->_set_stroke_color($color);
    $this->_set_line_style($width, "butt", "", $style);
    $y1 = $this->y($y1) - $h;
    $this->_pdf->rect($x1, $y1, $w, $h);
    $this->_pdf->stroke();
  }
  function filled_rectangle($x1, $y1, $w, $h, $color) {
    $this->_set_fill_color($color);
    $y1 = $this->y($y1) - $h;
    $this->_pdf->rect(floatval($x1), floatval($y1), floatval($w), floatval($h));
    $this->_pdf->fill();
  }
  function clipping_rectangle($x1, $y1, $w, $h) {
    $this->_pdf->save();
    $y1 = $this->y($y1) - $h;
    $this->_pdf->rect(floatval($x1), floatval($y1), floatval($w), floatval($h));
    $this->_pdf->clip();
  }
  function clipping_roundrectangle($x1, $y1, $w, $h, $rTL, $rTR, $rBR, $rBL) {
    $this->clipping_rectangle($x1, $y1, $w, $h);
  }
  function clipping_end() {
    $this->_pdf->restore();
  }
  function save() {
    $this->_pdf->save();
  }
  function restore() {
    $this->_pdf->restore();
  }
  function rotate($angle, $x, $y) {
    $pdf = $this->_pdf;
    $pdf->translate($x, $this->_height-$y);
    $pdf->rotate(-$angle);
    $pdf->translate(-$x, -$this->_height+$y);
  }
  function skew($angle_x, $angle_y, $x, $y) {
    $pdf = $this->_pdf;
    $pdf->translate($x, $this->_height-$y);
    $pdf->skew($angle_y, $angle_x); 
    $pdf->translate(-$x, -$this->_height+$y);
  }
  function scale($s_x, $s_y, $x, $y) {
    $pdf = $this->_pdf;
    $pdf->translate($x, $this->_height-$y);
    $pdf->scale($s_x, $s_y);
    $pdf->translate(-$x, -$this->_height+$y);
  }
  function translate($t_x, $t_y) {
    $this->_pdf->translate($t_x, -$t_y);
  }
  function transform($a, $b, $c, $d, $e, $f) {
    $this->_pdf->concat($a, $b, $c, $d, $e, $f);
  }
  function polygon($points, $color, $width = null, $style = null, $fill = false) {
    $this->_set_fill_color($color);
    $this->_set_stroke_color($color);
    if ( !$fill && isset($width) )
      $this->_set_line_style($width, "square", "miter", $style);
    $y = $this->y(array_pop($points));
    $x = array_pop($points);
    $this->_pdf->moveto($x,$y);
    while (count($points) > 1) {
      $y = $this->y(array_pop($points));
      $x = array_pop($points);
      $this->_pdf->lineto($x,$y);
    }
    if ( $fill )
      $this->_pdf->fill();
    else
      $this->_pdf->closepath_stroke();
  }
  function circle($x, $y, $r, $color, $width = null, $style = null, $fill = false) {
    $this->_set_fill_color($color);
    $this->_set_stroke_color($color);
    if ( !$fill && isset($width) )
      $this->_set_line_style($width, "round", "round", $style);
    $y = $this->y($y);
    $this->_pdf->circle($x, $y, $r);
    if ( $fill )
      $this->_pdf->fill();
    else
      $this->_pdf->stroke();
  }
  function image($img_url, $x, $y, $w, $h, $resolution = "normal") {
    $w = (int)$w;
    $h = (int)$h;
    $img_type = Image_Cache::detect_type($img_url);
    $img_ext  = Image_Cache::type_to_ext($img_type);
    if ( !isset($this->_imgs[$img_url]) ) {
      $this->_imgs[$img_url] = $this->_pdf->load_image($img_ext, $img_url, "");
    }
    $img = $this->_imgs[$img_url];
    $y = $this->y($y) - $h;
    $this->_pdf->fit_image($img, $x, $y, 'boxsize={'."$w $h".'} fitmethod=entire');
  }
  function text($x, $y, $text, $font, $size, $color = array(0,0,0), $word_spacing = 0, $char_spacing = 0, $angle = 0) {
    $fh = $this->_load_font($font);
    $this->_pdf->setfont($fh, $size);
    $this->_set_fill_color($color);
    $y = $this->y($y) - Font_Metrics::get_font_height($font, $size);
    $word_spacing = (float)$word_spacing;
    $char_spacing = (float)$char_spacing;
    $angle        = -(float)$angle;
    $this->_pdf->fit_textline($text, $x, $y, "rotate=$angle wordspacing=$word_spacing charspacing=$char_spacing ");
  }
  function javascript($code) {
    if ( defined("DOMPDF_PDFLIB_LICENSE") ) {
      $this->_pdf->create_action("JavaScript", $code);
    }
  }
  function add_named_dest($anchorname) {
    $this->_pdf->add_nameddest($anchorname,"");
  }
  function add_link($url, $x, $y, $width, $height) {
    $y = $this->y($y) - $height;
    if ( strpos($url, '#') === 0 ) {
      $name = substr($url,1);
      if ( $name )
        $this->_pdf->create_annotation($x, $y, $x + $width, $y + $height, 'Link', "contents={$url} destname=". substr($url,1) . " linewidth=0");
    } else {
      list($proto, $host, $path, $file) = explode_url($url);
      if ( $proto == "" || $proto === "file:
        return; 
      $url = build_url($proto, $host, $path, $file);
      $url = '{' . rawurldecode($url) . '}';
      $action = $this->_pdf->create_action("URI", "url=" . $url);
      $this->_pdf->create_annotation($x, $y, $x + $width, $y + $height, 'Link', "contents={$url} action={activate=$action} linewidth=0");
    }
  }
  function get_text_width($text, $font, $size, $word_spacing = 0, $letter_spacing = 0) {
    $fh = $this->_load_font($font);
    $num_spaces = mb_substr_count($text," ");
    $delta = $word_spacing * $num_spaces;
    if ( $letter_spacing ) {
      $num_chars = mb_strlen($text);
      $delta += ($num_chars - $num_spaces) * $letter_spacing;
    }
    return $this->_pdf->stringwidth($text, $fh, $size) + $delta;
  }
  function get_font_height($font, $size) {
    $fh = $this->_load_font($font);
    $this->_pdf->setfont($fh, $size);
    $asc = $this->_pdf->get_value("ascender", $fh);
    $desc = $this->_pdf->get_value("descender", $fh);
    $ratio = $this->_dompdf->get_option("font_height_ratio");
    return $size * ($asc - $desc) * $ratio;
  }
  function get_font_baseline($font, $size) {
    $ratio = $this->_dompdf->get_option("font_height_ratio");
    return $this->get_font_height($font, $size) / $ratio * 1.1;
  }
  function page_text($x, $y, $text, $font, $size, $color = array(0,0,0), $word_space = 0.0, $char_space = 0.0, $angle = 0.0) {
    $_t = "text";
    $this->_page_text[] = compact("_t", "x", "y", "text", "font", "size", "color", "word_space", "char_space", "angle");
  }
  function page_script($code, $type = "text/php") {
    $_t = "script";
    $this->_page_text[] = compact("_t", "code", "type");
  }
  function new_page() {
    $this->_place_objects();
    $this->_pdf->suspend_page("");
    $this->_pdf->begin_page_ext($this->_width, $this->_height, "");
    $this->_page_number = ++$this->_page_count;
  }
  protected function _add_page_text() {
    if ( !count($this->_page_text) )
      return;
    $this->_pdf->suspend_page("");
    for ($p = 1; $p <= $this->_page_count; $p++) {
      $this->_pdf->resume_page("pagenumber=$p");
      foreach ($this->_page_text as $pt) {
        extract($pt);
        switch ($_t) {
        case "text":
          $text = str_replace(array("{PAGE_NUM}","{PAGE_COUNT}"),
                              array($p, $this->_page_count), $text);
          $this->text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
          break;
        case "script":
          if (!$eval) {
            $eval = new PHP_Evaluator($this);
          }
          $eval->evaluate($code, array('PAGE_NUM' => $p, 'PAGE_COUNT' => $this->_page_count));
          break;
        }
      }
      $this->_pdf->suspend_page("");
    }
    $this->_pdf->resume_page("pagenumber=".$this->_page_number);
  }
  function stream($filename, $options = null) {
    $this->_add_page_text();
    if ( isset($options["compress"]) && $options["compress"] != 1 )
      $this->_pdf->set_value("compress", 0);
    else
      $this->_pdf->set_value("compress", 6);
    $this->_close();
    $data = "";
    if ( self::$IN_MEMORY ) {
      $data = $this->_pdf->get_buffer();
    } else {
    }
    $filename = str_replace(array("\n","'"),"", $filename);
    $attach = (isset($options["Attachment"]) && $options["Attachment"]) ? "attachment" : "inline";
    header("Cache-Control: private");
    header("Content-type: application/pdf");
    header("Content-Disposition: $attach; filename=\"$filename\"");
    if ( self::$IN_MEMORY )
      echo $data;
    else {
      $chunk = (1 << 21); 
      $fh = fopen($this->_file, "rb");
      if ( !$fh )
        throw new DOMPDF_Exception("Unable to load temporary PDF file: " . $this->_file);
      while ( !feof($fh) )
        echo fread($fh,$chunk);
      fclose($fh);
      if (DEBUGPNG) print '[pdflib stream unlink '.$this->_file.']';
      if (!DEBUGKEEPTEMP)
      unlink($this->_file);
      $this->_file = null;
      unset($this->_file);
    }
    flush();
  }
  function output($options = null) {
    $this->_add_page_text();
    if ( isset($options["compress"]) && $options["compress"] != 1 )
      $this->_pdf->set_value("compress", 0);
    else
      $this->_pdf->set_value("compress", 6);
    $this->_close();
    if ( self::$IN_MEMORY )
      $data = $this->_pdf->get_buffer();
    else {
      $data = file_get_contents($this->_file);
      if (DEBUGPNG) print '[pdflib output unlink '.$this->_file.']';
      if (!DEBUGKEEPTEMP)
      unlink($this->_file);
      $this->_file = null;
      unset($this->_file);
    }
    return $data;
  }
}
PDFLib_Adapter::$PAPER_SIZES = CPDF_Adapter::$PAPER_SIZES;