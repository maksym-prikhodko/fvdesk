<?php
class Null_Frame_Reflower extends Frame_Reflower {
  function __construct(Frame $frame) { parent::__construct($frame); }
  function reflow(Block_Frame_Decorator $block = null) { return; }
}
