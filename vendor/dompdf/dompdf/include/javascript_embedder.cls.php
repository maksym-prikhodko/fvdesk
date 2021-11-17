<?php
class Javascript_Embedder {
  protected $_dompdf;
  function __construct(DOMPDF $dompdf) {
    $this->_dompdf = $dompdf;
  }
  function insert($script) {
    $this->_dompdf->get_canvas()->javascript($script);
  }
  function render(Frame $frame) {
    if ( !$this->_dompdf->get_option("enable_javascript") ) {
      return;
    }
    $this->insert($frame->get_node()->nodeValue);
  }
}
