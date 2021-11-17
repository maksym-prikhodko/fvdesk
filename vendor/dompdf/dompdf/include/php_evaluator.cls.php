<?php
class PHP_Evaluator {
  protected $_canvas;
  function __construct(Canvas $canvas) {
    $this->_canvas = $canvas;
  }
  function evaluate($code, $vars = array()) {
    if ( !$this->_canvas->get_dompdf()->get_option("enable_php") ) {
      return;
    }
    $pdf = $this->_canvas;
    $PAGE_NUM = $pdf->get_page_number();
    $PAGE_COUNT = $pdf->get_page_count();
    foreach ($vars as $k => $v) {
      $$k = $v;
    }
    eval($code); 
  }
  function render(Frame $frame) {
    $this->evaluate($frame->get_node()->nodeValue);
  }
}
