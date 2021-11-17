<?php
define('__DEFAULT_STYLESHEET', DOMPDF_LIB_DIR . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "html.css");
class Stylesheet {
  const DEFAULT_STYLESHEET = __DEFAULT_STYLESHEET; 
  const ORIG_UA = 1;
  const ORIG_USER = 2;
  const ORIG_AUTHOR = 3;
  private static $_stylesheet_origins = array(
    self::ORIG_UA =>     -0x0FFFFFFF, 
    self::ORIG_USER =>   -0x0000FFFF, 
    self::ORIG_AUTHOR =>  0x00000000, 
  );
  private $_dompdf;
  private $_styles;
  private $_protocol;
  private $_base_host;
  private $_base_path;
  private $_page_styles;
  private $_loaded_files;
  private $_current_origin = self::ORIG_UA;
  static $ACCEPTED_DEFAULT_MEDIA_TYPE = "print";
  static $ACCEPTED_GENERIC_MEDIA_TYPES = array("all", "static", "visual", "bitmap", "paged", "dompdf");
  function __construct(DOMPDF $dompdf) {
    $this->_dompdf = $dompdf;
    $this->_styles = array();
    $this->_loaded_files = array();
    list($this->_protocol, $this->_base_host, $this->_base_path) = explode_url($_SERVER["SCRIPT_FILENAME"]);
    $this->_page_styles = array("base" => null);
  }
  function __destruct() {
    clear_object($this);
  }
  function set_protocol($protocol) { $this->_protocol = $protocol; }
  function set_host($host) { $this->_base_host = $host; }
  function set_base_path($path) { $this->_base_path = $path; }
  function get_dompdf() { return $this->_dompdf; }
  function get_protocol() { return $this->_protocol; }
  function get_host() { return $this->_base_host; }
  function get_base_path() { return $this->_base_path; }
  function get_page_styles() { return $this->_page_styles; }
  function add_style($key, Style $style) {
    if ( !is_string($key) ) {
      throw new DOMPDF_Exception("CSS rule must be keyed by a string.");
    }
    if ( isset($this->_styles[$key]) ) {
      $this->_styles[$key]->merge($style);
    }
    else {
      $this->_styles[$key] = clone $style;
    }
    $this->_styles[$key]->set_origin( $this->_current_origin );
  }
  function lookup($key) {
    if ( !isset($this->_styles[$key]) ) {
      return null;
    }
    return $this->_styles[$key];
  }
  function create_style(Style $parent = null) {
    return new Style($this, $this->_current_origin);
  }
  function load_css(&$css) { $this->_parse_css($css); }
  function load_css_file($file, $origin = self::ORIG_AUTHOR) {
    if ( $origin ) {
      $this->_current_origin = $origin;
    }
    if ( isset($this->_loaded_files[$file]) ) {
      return;
    }
    $this->_loaded_files[$file] = true;
    if ( strpos($file, "data:") === 0) {
      $parsed = parse_data_uri($file);
      $css = $parsed["data"];
    }
    else {
      $parsed_url = explode_url($file);
      list($this->_protocol, $this->_base_host, $this->_base_path, $filename) = $parsed_url;
      if ( $this->_protocol == "" ) {
        $file = $this->_base_path . $filename;
      }
      else {
        $file = build_url($this->_protocol, $this->_base_host, $this->_base_path, $filename);
      }
      set_error_handler("record_warnings");
      $css = file_get_contents($file, null, $this->_dompdf->get_http_context());
      restore_error_handler();
      $good_mime_type = true;
      if ( isset($http_response_header) && !$this->_dompdf->get_quirksmode() ) {
        foreach($http_response_header as $_header) {
          if ( preg_match("@Content-Type:\s*([\w/]+)@i", $_header, $matches) && 
              ($matches[1] !== "text/css") ) {
            $good_mime_type = false;
          }
        }
      }
      if ( !$good_mime_type || $css == "" ) {
        record_warnings(E_USER_WARNING, "Unable to load css file $file", __FILE__, __LINE__);
        return;
      }
    }
    $this->_parse_css($css);
  }
  private function _specificity($selector, $origin = self::ORIG_AUTHOR) {
    $a = ($selector === "!attr") ? 1 : 0;
    $b = min(mb_substr_count($selector, "#"), 255);
    $c = min(mb_substr_count($selector, ".") +
             mb_substr_count($selector, "["), 255);
    $d = min(mb_substr_count($selector, " ") + 
             mb_substr_count($selector, ">") +
             mb_substr_count($selector, "+"), 255);
    if ( !in_array($selector[0], array(" ", ">", ".", "#", "+", ":", "["))) {
      $d++;
    }
    if (DEBUGCSS) {
        print "<pre>\n";
        printf("_specificity(): 0x%08x \"%s\"\n", ($a << 24) | ($b << 16) | ($c << 8) | ($d), $selector);
        print "</pre>";
    }
    return self::$_stylesheet_origins[$origin] + ($a << 24) | ($b << 16) | ($c << 8) | ($d);
  }
  private function _css_selector_to_xpath($selector, $first_pass = false) {
    $query = "
    $pseudo_elements = array();
    $delimiters = array(" ", ">", ".", "#", "+", ":", "[", "(");
    if ( $selector[0] === "[" ) {
      $selector = "*$selector";
    }
    if ( !in_array($selector[0], $delimiters) ) {
      $selector = " $selector";
    }
    $tok = "";
    $len = mb_strlen($selector);
    $i = 0;
    while ( $i < $len ) {
      $s = $selector[$i];
      $i++;
      $tok = "";
      $in_attr = false;
      while ($i < $len) {
        $c = $selector[$i];
        $c_prev = $selector[$i-1];
        if ( !$in_attr && in_array($c, $delimiters) ) {
          break;
        }
        if ( $c_prev === "[" ) {
          $in_attr = true;
        }
        $tok .= $selector[$i++];
        if ( $in_attr && $c === "]" ) {
          $in_attr = false;
          break;
        }
      }
      switch ($s) {
      case " ":
      case ">":
        $expr = $s === " " ? "descendant" : "child";
        if ( mb_substr($query, -1, 1) !== "/" ) {
          $query .= "/";
        }
        $tok = strtolower($tok);
        if ( !$tok ) {
          $tok = "*";
        }
        $query .= "$expr::$tok";
        $tok = "";
        break;
      case ".":
      case "#":
        $attr = $s === "." ? "class" : "id";
        if ( mb_substr($query, -1, 1) === "/" ) {
          $query .= "*";
        }
        $query .= "[contains(concat(' ', @$attr, ' '), concat(' ', '$tok', ' '))]";
        $tok = "";
        break;
      case "+":
        if ( mb_substr($query, -1, 1) !== "/" ) {
          $query .= "/";
        }
        $query .= "following-sibling::$tok";
        $tok = "";
        break;
      case ":":
        $i2 = $i-strlen($tok)-2; 
        if ( !isset($selector[$i2]) || in_array($selector[$i2], $delimiters) ) {
          $query .= "*";
        }
        $last = false;
        switch ($tok) {
        case "first-child":
          $query .= "[1]";
          $tok = "";
          break;
        case "last-child":
          $query .= "[not(following-sibling::*)]";
          $tok = "";
          break;
        case "first-of-type":
          $query .= "[position() = 1]";
          $tok = "";
          break;
        case "last-of-type":
          $query .= "[position() = last()]";
          $tok = "";
          break;
        case "nth-last-of-type":
        case "nth-last-child":
          $last = true;
        case "nth-of-type":
        case "nth-child":
          $p = $i+1;
          $nth = trim(mb_substr($selector, $p, strpos($selector, ")", $i)-$p));
          if ( preg_match("/^\d+$/", $nth) ) {
            $condition = "position() = $nth";
          }
          elseif ( $nth === "odd" ) {
            $condition = "(position() mod 2) = 1";
          }
          elseif ( $nth === "even" ) {
            $condition = "(position() mod 2) = 0";
          }
          else {
            $condition = $this->_selector_an_plus_b($nth, $last);
          }
          $query .= "[$condition]";
          $tok = "";
          break;
        case "link":
          $query .= "[@href]";
          $tok = "";
          break;
        case "first-line": 
        case "first-letter": 
        case "active":
        case "hover":
        case "visited":
          $query .= "[false()]";
          $tok = "";
          break;
        case "before":
        case "after":
          if ( $first_pass ) {
            $pseudo_elements[$tok] = $tok;
          }
          else {
            $query .= "
  function apply_styles(Frame_Tree $tree) {
    $styles = array();
    $xp = new DOMXPath($tree->get_dom());
    foreach ($this->_styles as $selector => $style) {
      if ( strpos($selector, ":before") === false && strpos($selector, ":after") === false ) {
        continue;
      }
      $query = $this->_css_selector_to_xpath($selector, true);
      $nodes = @$xp->query( '.'.$query["query"] );
      if ( $nodes == null ) {
        record_warnings(E_USER_WARNING, "The CSS selector '$selector' is not valid", __FILE__, __LINE__);
        continue;
      }
      foreach ($nodes as $node) {
        foreach ($query["pseudo_elements"] as $pos) {
          if ( $node->hasAttribute("dompdf_{$pos}_frame_id") ) {
            continue;
          }
          if (($src = $this->_image($style->content)) !== "none") {
            $new_node = $node->ownerDocument->createElement("img_generated");
            $new_node->setAttribute("src", $src);
          }
          else {
            $new_node = $node->ownerDocument->createElement("dompdf_generated");
          }
          $new_node->setAttribute($pos, $pos);
          $new_frame_id = $tree->insert_node($node, $new_node, $pos);
          $node->setAttribute("dompdf_{$pos}_frame_id", $new_frame_id);
        }
      }
    }
    foreach ($this->_styles as $selector => $style) {
      $query = $this->_css_selector_to_xpath($selector);
      $nodes = @$xp->query($query["query"]);
      if ( $nodes == null ) {
        record_warnings(E_USER_WARNING, "The CSS selector '$selector' is not valid", __FILE__, __LINE__);
        continue;
      }
      foreach ($nodes as $node) {
        if ( $node->nodeType != XML_ELEMENT_NODE ) {
          continue;
        }
        $id = $node->getAttribute("frame_id");
        $spec = $this->_specificity($selector);
        $styles[$id][$spec][] = $style;
      }
    }
    $root_flg = false;
    foreach ($tree->get_frames() as $frame) {
      if ( !$root_flg && $this->_page_styles["base"] ) {
        $style = $this->_page_styles["base"];
        $root_flg = true;
      }
      else {
        $style = $this->create_style();
      }
      $p = $frame;
      while ( $p = $p->get_parent() ) {
        if ( $p->get_node()->nodeType == XML_ELEMENT_NODE ) {
          break;
        }
      }
      if ( $frame->get_node()->nodeType != XML_ELEMENT_NODE ) {
        if ( $p ) {
          $style->inherit($p->get_style());
        }
        $frame->set_style($style);
        continue;
      }
      $id = $frame->get_id();
      Attribute_Translator::translate_attributes($frame);
      if ( ($str = $frame->get_node()->getAttribute(Attribute_Translator::$_style_attr)) !== "" ) {
        $styles[$id][1][] = $this->_parse_properties($str);
      }
      if ( ($str = $frame->get_node()->getAttribute("style")) !== "" ) {
        $str = preg_replace("'/\*.*?\*/'si", "", $str);
        $spec = $this->_specificity("!attr");
        $styles[$id][$spec][] = $this->_parse_properties($str);
      }
      if ( isset($styles[$id]) ) {
        $applied_styles = $styles[ $frame->get_id() ];
        ksort($applied_styles);
        if (DEBUGCSS) {
          $debug_nodename = $frame->get_node()->nodeName;
          print "<pre>\n[$debug_nodename\n";
          foreach ($applied_styles as $spec => $arr) {
            printf("specificity: 0x%08x\n",$spec);
            foreach ($arr as $s) {
              print "[\n";
              $s->debug_print();
              print "]\n";
            }
          }
        }
        foreach ($applied_styles as $arr) {
          foreach ($arr as $s) {
            $style->merge($s);
          }
        }
      }
      if ( $p ) {
        if (DEBUGCSS) {
          print "inherit:\n";
          print "[\n";
          $p->get_style()->debug_print();
          print "]\n";
        }
        $style->inherit( $p->get_style() );
      }
      if (DEBUGCSS) {
        print "DomElementStyle:\n";
        print "[\n";
        $style->debug_print();
        print "]\n";
        print "/$debug_nodename]\n</pre>";
      }
      $frame->set_style($style);
    }
    foreach ( array_keys($this->_styles) as $key ) {
      $this->_styles[$key] = null;
      unset($this->_styles[$key]);
    }
  }
  private function _parse_css($str) {
    $str = trim($str);
    $css = preg_replace(array(
      "'/\*.*?\*/'si", 
      "/^<!--/",
      "/-->$/"
    ), "", $str);
    $re =
      "/\s*                                   # Skip leading whitespace                             \n".
      "( @([^\s{]+)\s*([^{;]*) (?:;|({)) )?   # Match @rules followed by ';' or '{'                 \n".
      "(?(1)                                  # Only parse sub-sections if we're in an @rule...     \n".
      "  (?(4)                                # ...and if there was a leading '{'                   \n".
      "    \s*( (?:(?>[^{}]+) ({)?            # Parse rulesets and individual @page rules           \n".
      "            (?(6) (?>[^}]*) }) \s*)+?                                                        \n".
      "       )                                                                                     \n".
      "   })                                  # Balancing '}'                                       \n".
      "|                                      # Branch to match regular rules (not preceeded by '@')\n".
      "([^{]*{[^}]*}))                        # Parse normal rulesets\n".
      "/xs";
    if ( preg_match_all($re, $css, $matches, PREG_SET_ORDER) === false ) {
      throw new DOMPDF_Exception("Error parsing css file: preg_match_all() failed.");
    }
    foreach ( $matches as $match ) {
      $match[2] = trim($match[2]);
      if ( $match[2] !== "" ) {
        switch ($match[2]) {
        case "import":
          $this->_parse_import($match[3]);
          break;
        case "media":
          $acceptedmedia = self::$ACCEPTED_GENERIC_MEDIA_TYPES;
          $acceptedmedia[] = $this->_dompdf->get_option("default_media_type");
          $media = preg_split("/\s*,\s*/", mb_strtolower(trim($match[3])));
          if ( count(array_intersect($acceptedmedia, $media)) ) {
            $this->_parse_sections($match[5]);
          }
          break;
        case "page":
          $page_selector = trim($match[3]);
          $key = null;
          switch($page_selector) {
            case "": 
              $key = "base"; 
              break;
            case ":left":
            case ":right":
            case ":odd":
            case ":even":
            case ":first":
              $key = $page_selector;
            default: continue;
          }
          if ( empty($this->_page_styles[$key]) ) {
            $this->_page_styles[$key] = $this->_parse_properties($match[5]);
          }
          else {
            $this->_page_styles[$key]->merge($this->_parse_properties($match[5]));
          }
          break;
        case "font-face":
          $this->_parse_font_face($match[5]);
          break;
        default:
          break;
        }
        continue;
      }
      if ( $match[7] !== "" ) {
        $this->_parse_sections($match[7]);
      }
    }
  }
  protected function _image($val) {
    $DEBUGCSS=DEBUGCSS;
    $parsed_url = "none";
    if ( mb_strpos($val, "url") === false ) {
      $path = "none"; 
    }
    else {
      $val = preg_replace("/url\(['\"]?([^'\")]+)['\"]?\)/","\\1", trim($val));
      $parsed_url = explode_url($val);
      if ( $parsed_url["protocol"] == "" && $this->get_protocol() == "" ) {
        if ($parsed_url["path"][0] === '/' || $parsed_url["path"][0] === '\\' ) {
          $path = $_SERVER["DOCUMENT_ROOT"].'/';
        }
        else {
          $path = $this->get_base_path();
        }
        $path .= $parsed_url["path"] . $parsed_url["file"];
        $path = realpath($path);
        if (!$path) { $path = 'none'; }
      }
      else {
        $path = build_url($this->get_protocol(),
                          $this->get_host(),
                          $this->get_base_path(),
                          $val);
      }
    }
    if ($DEBUGCSS) {
      print "<pre>[_image\n";
      print_r($parsed_url);
      print $this->get_protocol()."\n".$this->get_base_path()."\n".$path."\n";
      print "_image]</pre>";;
    }
    return $path;
  }
  private function _parse_import($url) {
    $arr = preg_split("/[\s\n,]/", $url,-1, PREG_SPLIT_NO_EMPTY);
    $url = array_shift($arr);
    $accept = false;
    if ( count($arr) > 0 ) {
      $acceptedmedia = self::$ACCEPTED_GENERIC_MEDIA_TYPES;
      $acceptedmedia[] = $this->_dompdf->get_option("default_media_type");
      foreach ( $arr as $type ) {
        if ( in_array(mb_strtolower(trim($type)), $acceptedmedia) ) {
          $accept = true;
          break;
        }
      }
    }
    else {
      $accept = true;
    }
    if ( $accept ) {
      $protocol = $this->_protocol;
      $host = $this->_base_host;
      $path = $this->_base_path;
      $url = $this->_image($url);
      $this->load_css_file($url);
      $this->_protocol = $protocol;
      $this->_base_host = $host;
      $this->_base_path = $path;
    }
  }
  private function _parse_font_face($str) {
    $descriptors = $this->_parse_properties($str);
    preg_match_all("/(url|local)\s*\([\"\']?([^\"\'\)]+)[\"\']?\)\s*(format\s*\([\"\']?([^\"\'\)]+)[\"\']?\))?/i", $descriptors->src, $src);
    $sources = array();
    $valid_sources = array();
    foreach($src[0] as $i => $value) {
      $source = array(
        "local"  => strtolower($src[1][$i]) === "local",
        "uri"    => $src[2][$i],
        "format" => $src[4][$i],
        "path"   => build_url($this->_protocol, $this->_base_host, $this->_base_path, $src[2][$i]),
      );
      if ( !$source["local"] && in_array($source["format"], array("", "woff", "opentype", "truetype")) ) {
        $valid_sources[] = $source;
      }
      $sources[] = $source;
    }
    if ( empty($valid_sources) ) {
      return;
    }
    $style = array(
      "family" => $descriptors->get_font_family_raw(),
      "weight" => $descriptors->font_weight,
      "style"  => $descriptors->font_style,
    );
    Font_Metrics::register_font($style, $valid_sources[0]["path"]);
  }
  private function _parse_properties($str) {
    $properties = preg_split("/;(?=(?:[^\(]*\([^\)]*\))*(?![^\)]*\)))/", $str);
    if (DEBUGCSS) print '[_parse_properties';
    $style = new Style($this);
    foreach ($properties as $prop) {
      if (DEBUGCSS) print '(';
      $important = false;
      $prop = trim($prop);
      if ( substr($prop, -9) === 'important' ) {
        $prop_tmp = rtrim(substr($prop, 0, -9));
        if ( substr($prop_tmp, -1) === '!' ) {
          $prop = rtrim(substr($prop_tmp, 0, -1));
          $important = true;
        }
      }
      if ( $prop === "" ) {
        if (DEBUGCSS) print 'empty)';
        continue;
      }
      $i = mb_strpos($prop, ":");
      if ( $i === false ) {
        if (DEBUGCSS) print 'novalue'.$prop.')';
        continue;
      }
      $prop_name = rtrim(mb_strtolower(mb_substr($prop, 0, $i)));
      $value = ltrim(mb_substr($prop, $i+1));
      if (DEBUGCSS) print $prop_name.':='.$value.($important?'!IMPORTANT':'').')';
      if ($important) {
        $style->important_set($prop_name);
      }
      $style->$prop_name = $value;
    }
    if (DEBUGCSS) print '_parse_properties]';
    return $style;
  }
  private function _parse_sections($str) {
    $patterns = array("/[\\s\n]+/", "/\\s+([>.:+#])\\s+/");
    $replacements = array(" ", "\\1");
    $str = preg_replace($patterns, $replacements, $str);
    $sections = explode("}", $str);
    if (DEBUGCSS) print '[_parse_sections';
    foreach ($sections as $sect) {
      $i = mb_strpos($sect, "{");
      $selectors = explode(",", mb_substr($sect, 0, $i));
      if (DEBUGCSS) print '[section';
      $style = $this->_parse_properties(trim(mb_substr($sect, $i+1)));
      foreach ($selectors as $selector) {
        $selector = trim($selector);
        if ($selector == "") {
          if (DEBUGCSS) print '#empty#';
          continue;
        }
        if (DEBUGCSS) print '#'.$selector.'#';
        $this->add_style($selector, $style);
      }
      if (DEBUGCSS) print 'section]';
    }
    if (DEBUGCSS) print '_parse_sections]';
  }
  function __toString() {
    $str = "";
    foreach ($this->_styles as $selector => $style) {
      $str .= "$selector => " . $style->__toString() . "\n";
    }
    return $str;
  }
}