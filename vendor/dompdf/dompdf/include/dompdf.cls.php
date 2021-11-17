<?php
class DOMPDF {
  protected $_xml;
  protected $_tree;
  protected $_css;
  protected $_pdf;
  protected $_paper_size;
  protected $_paper_orientation;
  protected $_callbacks;
  private $_cache_id;
  protected $_base_host;
  protected $_base_path;
  protected $_protocol;
  protected $_http_context;
  private $_start_time = null;
  private $_system_locale = null;
  private $_locale_standard = false;
  private $_default_view = "Fit";
  private $_default_view_options = array();
  private $_quirksmode = false;
  public static $native_fonts = array(
    "courier", "courier-bold", "courier-oblique", "courier-boldoblique",
    "helvetica", "helvetica-bold", "helvetica-oblique", "helvetica-boldoblique",
    "times-roman", "times-bold", "times-italic", "times-bolditalic",
    "symbol", "zapfdinbats"
  );
  private $_options = array(
    "temp_dir"                 => DOMPDF_TEMP_DIR,
    "font_dir"                 => DOMPDF_FONT_DIR,
    "font_cache"               => DOMPDF_FONT_CACHE,
    "chroot"                   => DOMPDF_CHROOT,
    "log_output_file"          => DOMPDF_LOG_OUTPUT_FILE,
    "default_media_type"       => DOMPDF_DEFAULT_MEDIA_TYPE,
    "default_paper_size"       => DOMPDF_DEFAULT_PAPER_SIZE,
    "default_font"             => DOMPDF_DEFAULT_FONT,
    "dpi"                      => DOMPDF_DPI,
    "font_height_ratio"        => DOMPDF_FONT_HEIGHT_RATIO,
    "enable_unicode"           => DOMPDF_UNICODE_ENABLED,
    "enable_php"               => DOMPDF_ENABLE_PHP,
    "enable_remote"            => DOMPDF_ENABLE_REMOTE,
    "enable_css_float"         => DOMPDF_ENABLE_CSS_FLOAT,
    "enable_javascript"        => DOMPDF_ENABLE_JAVASCRIPT,
    "enable_html5_parser"      => DOMPDF_ENABLE_HTML5PARSER,
    "enable_font_subsetting"   => DOMPDF_ENABLE_FONTSUBSETTING,
    "debug_png"                => DEBUGPNG,
    "debug_keep_temp"          => DEBUGKEEPTEMP,
    "debug_css"                => DEBUGCSS,
    "debug_layout"             => DEBUG_LAYOUT,
    "debug_layout_lines"       => DEBUG_LAYOUT_LINES,
    "debug_layout_blocks"      => DEBUG_LAYOUT_BLOCKS,
    "debug_layout_inline"      => DEBUG_LAYOUT_INLINE,
    "debug_layout_padding_box" => DEBUG_LAYOUT_PADDINGBOX,
    "admin_username"           => DOMPDF_ADMIN_USERNAME,
    "admin_password"           => DOMPDF_ADMIN_PASSWORD,
  );
  function __construct() {
    $this->_locale_standard = sprintf('%.1f', 1.0) == '1.0';
    $this->save_locale();
    $this->_messages = array();
    $this->_css = new Stylesheet($this);
    $this->_pdf = null;
    $this->_paper_size = DOMPDF_DEFAULT_PAPER_SIZE;
    $this->_paper_orientation = "portrait";
    $this->_base_protocol = "";
    $this->_base_host = "";
    $this->_base_path = "";
    $this->_http_context = null;
    $this->_callbacks = array();
    $this->_cache_id = null;
    $this->restore_locale();
  }
  function __destruct() {
    clear_object($this);
  }
  function get_option($key) {
    if ( !array_key_exists($key, $this->_options) ) {
      throw new DOMPDF_Exception("Option '$key' doesn't exist");
    }
    return $this->_options[$key];
  }
  function set_option($key, $value) {
    if ( !array_key_exists($key, $this->_options) ) {
      throw new DOMPDF_Exception("Option '$key' doesn't exist");
    }
    $this->_options[$key] = $value;
  }
  function set_options(array $options) {
    foreach ($options as $key => $value) {
      $this->set_option($key, $value);
    }
  }
  private function save_locale() {
    if ( $this->_locale_standard ) {
      return;
    }
    $this->_system_locale = setlocale(LC_NUMERIC, "0");
    setlocale(LC_NUMERIC, "C");
  }
  private function restore_locale() {
    if ( $this->_locale_standard ) {
      return;
    }
    setlocale(LC_NUMERIC, $this->_system_locale);
  }
  function get_tree() {
    return $this->_tree;
  }
  function set_protocol($proto) {
    $this->_protocol = $proto;
  }
  function set_host($host) {
    $this->_base_host = $host;
  }
  function set_base_path($path) {
    $this->_base_path = $path;
  }
  function set_http_context($http_context) {
    $this->_http_context = $http_context;
  }
  function set_default_view($default_view, $options) {
    $this->_default_view = $default_view;
    $this->_default_view_options = $options;
  }
  function get_protocol() {
    return $this->_protocol;
  }
  function get_host() {
    return $this->_base_host;
  }
  function get_base_path() {
    return $this->_base_path;
  }
  function get_http_context() {
    return $this->_http_context;
  }
  function get_canvas() {
    return $this->_pdf;
  }
  function get_callbacks() {
    return $this->_callbacks;
  }
  function get_css() {
    return $this->_css;
  }
  function get_dom() {
    return $this->_xml;
  }
  function load_html_file($file) {
    $this->save_locale();
    if ( !$this->_protocol && !$this->_base_host && !$this->_base_path ) {
      list($this->_protocol, $this->_base_host, $this->_base_path) = explode_url($file);
    }
    if ( !$this->get_option("enable_remote") && ($this->_protocol != "" && $this->_protocol !== "file:
      throw new DOMPDF_Exception("Remote file requested, but DOMPDF_ENABLE_REMOTE is false.");
    }
    if ($this->_protocol == "" || $this->_protocol === "file:
      $realfile = realpath($file);
      if ( !$realfile ) {
        throw new DOMPDF_Exception("File '$file' not found.");
      }
      $chroot = $this->get_option("chroot");
      if ( strpos($realfile, $chroot) !== 0 ) {
        throw new DOMPDF_Exception("Permission denied on $file. The file could not be found under the directory specified by DOMPDF_CHROOT.");
      }
      if ( substr(basename($realfile), 0, 1) === "." ) {
        throw new DOMPDF_Exception("Permission denied on $file.");
      }
      $file = $realfile;
    }
    $contents = file_get_contents($file, null, $this->_http_context);
    $encoding = null;
    if ( isset($http_response_header) ) {
      foreach($http_response_header as $_header) {
        if ( preg_match("@Content-Type:\s*[\w/]+;\s*?charset=([^\s]+)@i", $_header, $matches) ) {
          $encoding = strtoupper($matches[1]);
          break;
        }
      }
    }
    $this->restore_locale();
    $this->load_html($contents, $encoding);
  }
  function load_html($str, $encoding = null) {
    $this->save_locale();
    mb_detect_order('auto');
    if (mb_detect_encoding($str) !== 'UTF-8') {
      $metatags = array(
        '@<meta\s+http-equiv="Content-Type"\s+content="(?:[\w/]+)(?:;\s*?charset=([^\s"]+))?@i',
        '@<meta\s+content="(?:[\w/]+)(?:;\s*?charset=([^\s"]+))"?\s+http-equiv="Content-Type"@i',
        '@<meta [^>]*charset\s*=\s*["\']?\s*([^"\' ]+)@i',
      );
      foreach($metatags as $metatag) {
        if (preg_match($metatag, $str, $matches)) break;
      }
      if (mb_detect_encoding($str) == '') {
        if (isset($matches[1])) {
          $encoding = strtoupper($matches[1]);
        }
        else {
          $encoding = 'UTF-8';
        }
      }
      else {
        if ( isset($matches[1]) ) {
          $encoding = strtoupper($matches[1]);
        }
        else {
          $encoding = 'auto';
        }
      }
      if ( $encoding !== 'UTF-8' ) {
        $str = mb_convert_encoding($str, 'UTF-8', $encoding);
      }
      if ( isset($matches[1]) ) {
        $str = preg_replace('/charset=([^\s"]+)/i', 'charset=UTF-8', $str);
      }
      else {
        $str = str_replace('<head>', '<head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8">', $str);
      }
    }
    else {
      $encoding = 'UTF-8';
    }
    if ( substr($str, 0, 3) == chr(0xEF).chr(0xBB).chr(0xBF) ) {
      $str = substr($str, 3);
    }
    if ( $encoding !== 'UTF-8' ) {
      $re = '/<meta ([^>]*)((?:charset=[^"\' ]+)([^>]*)|(?:charset=["\'][^"\' ]+["\']))([^>]*)>/i';
      $str = preg_replace($re, '<meta $1$3>', $str);
    }
    set_error_handler("record_warnings");
    $quirksmode = false;
    if ( $this->get_option("enable_html5_parser") ) {
      $tokenizer = new HTML5_Tokenizer($str);
      $tokenizer->parse();
      $doc = $tokenizer->save();
      $tag_names = array("html", "table", "tbody", "thead", "tfoot", "tr");
      foreach($tag_names as $tag_name) {
        $nodes = $doc->getElementsByTagName($tag_name);
        foreach($nodes as $node) {
          self::remove_text_nodes($node);
        }
      }
      $quirksmode = ($tokenizer->getTree()->getQuirksMode() > HTML5_TreeBuilder::NO_QUIRKS);
    }
    else {
      $doc = new DOMDocument();
      $doc->preserveWhiteSpace = true;
      $doc->loadHTML( mb_convert_encoding( $str , 'HTML-ENTITIES' , 'UTF-8' ) );
      if ( preg_match("/^(.+)<!doctype/i", ltrim($str), $matches) ) {
        $quirksmode = true;
      }
      elseif ( !preg_match("/^<!doctype/i", ltrim($str), $matches) ) {
        $quirksmode = true;
      }
      else {
        if ( !$doc->doctype->publicId && !$doc->doctype->systemId ) {
          $quirksmode = false;
        }
        if ( !preg_match("/xhtml/i", $doc->doctype->publicId) ) {
          $quirksmode = true;
        }
      }
    }
    $this->_xml = $doc;
    $this->_quirksmode = $quirksmode;
    $this->_tree = new Frame_Tree($this->_xml);
    restore_error_handler();
    $this->restore_locale();
  }
  static function remove_text_nodes(DOMNode $node) {
    $children = array();
    for ($i = 0; $i < $node->childNodes->length; $i++) {
      $child = $node->childNodes->item($i);
      if ( $child->nodeName === "#text" ) {
        $children[] = $child;
      }
    }
    foreach($children as $child) {
      $node->removeChild($child);
    }
  }
  protected function _process_html() {
    $this->_tree->build_tree();
    $this->_css->load_css_file(Stylesheet::DEFAULT_STYLESHEET, Stylesheet::ORIG_UA);
    $acceptedmedia = Stylesheet::$ACCEPTED_GENERIC_MEDIA_TYPES;
    $acceptedmedia[] = $this->get_option("default_media_type");
    $base_nodes = $this->_xml->getElementsByTagName("base");
    if ( $base_nodes->length && ($href = $base_nodes->item(0)->getAttribute("href")) ) {
      list($this->_protocol, $this->_base_host, $this->_base_path) = explode_url($href);
    }
    $this->_css->set_protocol($this->_protocol);
    $this->_css->set_host($this->_base_host);
    $this->_css->set_base_path($this->_base_path);
    $xpath = new DOMXPath($this->_xml);
    $stylesheets = $xpath->query("
    foreach($stylesheets as $tag) {
      switch (strtolower($tag->nodeName)) {
        case "link":
          if ( mb_strtolower(stripos($tag->getAttribute("rel"), "stylesheet") !== false) || 
            mb_strtolower($tag->getAttribute("type")) === "text/css" ) {
            $formedialist = preg_split("/[\s\n,]/", $tag->getAttribute("media"),-1, PREG_SPLIT_NO_EMPTY);
            if ( count($formedialist) > 0 ) {
              $accept = false;
              foreach ( $formedialist as $type ) {
                if ( in_array(mb_strtolower(trim($type)), $acceptedmedia) ) {
                  $accept = true;
                  break;
                }
              }
              if (!$accept) {
                continue;
              }
            }
            $url = $tag->getAttribute("href");
            $url = build_url($this->_protocol, $this->_base_host, $this->_base_path, $url);
            $this->_css->load_css_file($url, Stylesheet::ORIG_AUTHOR);
          }
          break;
        case "style":
          if ( $tag->hasAttributes() &&
            ($media = $tag->getAttribute("media")) &&
            !in_array($media, $acceptedmedia) ) {
            continue;
          }
          $css = "";
          if ( $tag->hasChildNodes() ) {
            $child = $tag->firstChild;
            while ( $child ) {
              $css .= $child->nodeValue; 
              $child = $child->nextSibling;
            }
          }
          else {
            $css = $tag->nodeValue;
          }
          $this->_css->load_css($css);
          break;
      }
    }
  }
  function set_paper($size, $orientation = "portrait") {
    $this->_paper_size = $size;
    $this->_paper_orientation = $orientation;
  }
  function enable_caching($cache_id) {
    $this->_cache_id = $cache_id;
  }
  function set_callbacks($callbacks) {
    if (is_array($callbacks)) {
      $this->_callbacks = array();
      foreach ($callbacks as $c) {
        if (is_array($c) && isset($c['event']) && isset($c['f'])) {
          $event = $c['event'];
          $f = $c['f'];
          if (is_callable($f) && is_string($event)) {
            $this->_callbacks[$event][] = $f;
          }
        }
      }
    }
  }
  function get_quirksmode(){
    return $this->_quirksmode;
  }
  function parse_default_view($value) {
    $valid = array("XYZ", "Fit", "FitH", "FitV", "FitR", "FitB", "FitBH", "FitBV");
    $options = preg_split("/\s*,\s*/", trim($value));
    $default_view = array_shift($options);
    if ( !in_array($default_view, $valid) ) {
      return false;
    }
    $this->set_default_view($default_view, $options);
    return true;
  }
  function render() {
    $this->save_locale();
    $log_output_file = $this->get_option("log_output_file");
    if ( $log_output_file ) {
      if ( !file_exists($log_output_file) && is_writable(dirname($log_output_file)) ) {
        touch($log_output_file);
      }
      $this->_start_time = microtime(true);
      ob_start();
    }
    $this->_process_html();
    $this->_css->apply_styles($this->_tree);
    $page_styles = $this->_css->get_page_styles();
    $base_page_style = $page_styles["base"];
    unset($page_styles["base"]);
    foreach($page_styles as $_page_style) {
      $_page_style->inherit($base_page_style);
    }
    if ( is_array($base_page_style->size) ) {
      $this->set_paper(array(0, 0, $base_page_style->size[0], $base_page_style->size[1]));
    }
    $this->_pdf = Canvas_Factory::get_instance($this, $this->_paper_size, $this->_paper_orientation);
    Font_Metrics::init($this->_pdf);
    if ( $this->get_option("enable_font_subsetting") && $this->_pdf instanceof CPDF_Adapter ) {
      foreach ($this->_tree->get_frames() as $frame) {
        $style = $frame->get_style();
        $node  = $frame->get_node();
        if ( $node->nodeName === "#text" ) {
          $this->_pdf->register_string_subset($style->font_family, $node->nodeValue);
          continue;
        }
        if ( $style->display === "list-item" ) {
          $chars = List_Bullet_Renderer::get_counter_chars($style->list_style_type);
          $this->_pdf->register_string_subset($style->font_family, $chars);
          continue;
        }
        if ( $frame->get_node()->nodeName == "dompdf_generated" ) {
          $chars = List_Bullet_Renderer::get_counter_chars('decimal');
          $this->_pdf->register_string_subset($style->font_family, $chars);
          $chars = List_Bullet_Renderer::get_counter_chars('upper-alpha');
          $this->_pdf->register_string_subset($style->font_family, $chars);
          $chars = List_Bullet_Renderer::get_counter_chars('lower-alpha');
          $this->_pdf->register_string_subset($style->font_family, $chars);
          $chars = List_Bullet_Renderer::get_counter_chars('lower-greek');
          $this->_pdf->register_string_subset($style->font_family, $chars);
          $this->_pdf->register_string_subset($style->font_family, $style->content);
          continue;
        }
      }
    }
    $root = null;
    foreach ($this->_tree->get_frames() as $frame) {
      if ( is_null($root) ) {
        $root = Frame_Factory::decorate_root( $this->_tree->get_root(), $this );
        continue;
      }
      Frame_Factory::decorate_frame($frame, $this, $root);
    }
    $title = $this->_xml->getElementsByTagName("title");
    if ( $title->length ) {
      $this->_pdf->add_info("Title", trim($title->item(0)->nodeValue));
    }
    $metas = $this->_xml->getElementsByTagName("meta");
    $labels = array(
      "author" => "Author",
      "keywords" => "Keywords",
      "description" => "Subject",
    );
    foreach($metas as $meta) {
      $name = mb_strtolower($meta->getAttribute("name"));
      $value = trim($meta->getAttribute("content"));
      if ( isset($labels[$name]) ) {
        $this->_pdf->add_info($labels[$name], $value);
        continue;
      }
      if ( $name === "dompdf.view" && $this->parse_default_view($value) ) {
        $this->_pdf->set_default_view($this->_default_view, $this->_default_view_options);
      }
    }
    $root->set_containing_block(0, 0, $this->_pdf->get_width(), $this->_pdf->get_height());
    $root->set_renderer(new Renderer($this));
    $root->reflow();
    Image_Cache::clear();
    global $_dompdf_warnings, $_dompdf_show_warnings;
    if ( $_dompdf_show_warnings ) {
      echo '<b>DOMPDF Warnings</b><br><pre>';
      foreach ($_dompdf_warnings as $msg) {
        echo $msg . "\n";
      }
      echo $this->get_canvas()->get_cpdf()->messages;
      echo '</pre>';
      flush();
    }
    $this->restore_locale();
  }
  function add_info($label, $value) {
    if ( !is_null($this->_pdf) ) {
      $this->_pdf->add_info($label, $value);
    }
  }
  private function write_log() {
    $log_output_file = $this->get_option("log_output_file");
    if ( !$log_output_file || !is_writable($log_output_file) ) {
      return;
    }
    $frames = Frame::$ID_COUNTER;
    $memory = DOMPDF_memory_usage() / 1024;
    $time = (microtime(true) - $this->_start_time) * 1000;
    $out = sprintf(
      "<span style='color: #000' title='Frames'>%6d</span>".
        "<span style='color: #009' title='Memory'>%10.2f KB</span>".
        "<span style='color: #900' title='Time'>%10.2f ms</span>".
        "<span  title='Quirksmode'>  ".
        ($this->_quirksmode ? "<span style='color: #d00'> ON</span>" : "<span style='color: #0d0'>OFF</span>").
        "</span><br />", $frames, $memory, $time);
    $out .= ob_get_clean();
    $log_output_file = $this->get_option("log_output_file");
    file_put_contents($log_output_file, $out);
  }
  function stream($filename, $options = null) {
    $this->save_locale();
    $this->write_log();
    if ( !is_null($this->_pdf) ) {
      $this->_pdf->stream($filename, $options);
    }
    $this->restore_locale();
  }
  function output($options = null) {
    $this->save_locale();
    $this->write_log();
    if ( is_null($this->_pdf) ) {
      return null;
    }
    $output = $this->_pdf->output( $options );
    $this->restore_locale();
    return $output;
  }
  function output_html() {
    return $this->_xml->saveHTML();
  }
}
