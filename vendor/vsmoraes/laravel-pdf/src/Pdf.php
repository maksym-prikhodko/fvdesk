<?php
namespace Vsmoraes\Pdf;
interface Pdf
{
    public function load($html, $size = 'A4', $orientation = 'portrait');
    public function filename($filename = null);
    public function setPaper($size, $orientation);
    public function render();
    public function clear();
    public function show($options = ['compress' => 1, 'Attachment' => 0]);
    public function download($options = ['compress' => 1, 'Attachment' => 0]);
    public function output($options = ['compress' => 1]);
}
