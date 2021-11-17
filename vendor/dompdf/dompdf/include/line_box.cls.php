<?php
class Line_Box {
  protected $_block_frame;
  protected $_frames = array();
  public $wc = 0;
  public $y = null;
  public $w = 0.0;
  public $h = 0.0;
  public $left = 0.0;
  public $right = 0.0;
  public $tallest_frame = null;
  public $floating_blocks = array();
  public $br = false;
  function __construct(Block_Frame_Decorator $frame, $y = 0) {
    $this->_block_frame = $frame;
    $this->_frames = array();
    $this->y = $y;
    $this->get_float_offsets();
  }
  function get_floats_inside(Page_Frame_Decorator $root) {
    $floating_frames = $root->get_floating_frames();
    if ( count($floating_frames) == 0 ) {
      return $floating_frames;
    }
    $p = $this->_block_frame;
    while( $p->get_style()->float === "none" ) {
      $parent = $p->get_parent();
      if ( !$parent ) {
        break;
      }
      $p = $parent;
    }
    if ( $p == $root ) {
      return $floating_frames;
    }
    $parent = $p;
    $childs = array();
    foreach ($floating_frames as $_floating) {
      $p = $_floating->get_parent();
      while (($p = $p->get_parent()) && $p !== $parent);
      if ( $p ) {
        $childs[] = $p;
      }
    }
    return $childs;
  }
  function get_float_offsets() {
    $enable_css_float = $this->_block_frame->get_dompdf()->get_option("enable_css_float");
    if ( !$enable_css_float ) {
      return;
    }
    static $anti_infinite_loop = 500; 
    $reflower = $this->_block_frame->get_reflower();
    if ( !$reflower ) {
      return;
    }
    $cb_w = null;
    $block = $this->_block_frame;
    $root = $block->get_root();
    if ( !$root ) {
      return;
    }
    $floating_frames = $this->get_floats_inside($root);
    foreach ( $floating_frames as $child_key => $floating_frame ) {
      $id = $floating_frame->get_id();
      if ( isset($this->floating_blocks[$id]) ) {
        continue;
      }
      $floating_style = $floating_frame->get_style();
      $float = $floating_style->float;
      $floating_width = $floating_frame->get_margin_width();
      if (!$cb_w) {
        $cb_w = $floating_frame->get_containing_block("w");
      }
      $line_w = $this->get_width();
      if ( !$floating_frame->_float_next_line && ($cb_w <= $line_w + $floating_width) && ($cb_w > $line_w) ) {
        $floating_frame->_float_next_line = true;
        continue;
      }
      if ( $anti_infinite_loop-- > 0 &&
           $floating_frame->get_position("y") + $floating_frame->get_margin_height() > $this->y && 
           $block->get_position("x") + $block->get_margin_width() > $floating_frame->get_position("x")
           ) {
        if ( $float === "left" )
          $this->left  += $floating_width;
        else
          $this->right += $floating_width;
        $this->floating_blocks[$id] = true;
      }
      else {
        $root->remove_floating_frame($child_key);
      }
    }
  }
  function get_width(){
    return $this->left + $this->w + $this->right;
  }
  function get_block_frame() {
    return $this->_block_frame;
  }
  function &get_frames() {
    return $this->_frames;
  }
  function add_frame(Frame $frame) {
    $this->_frames[] = $frame;
  }
  function __toString(){
    $props = array("wc", "y", "w", "h", "left", "right", "br");
    $s = "";
    foreach($props as $prop) {
      $s .= "$prop: ".$this->$prop."\n";
    }
    $s .= count($this->_frames)." frames\n";
    return $s;
  }
}
