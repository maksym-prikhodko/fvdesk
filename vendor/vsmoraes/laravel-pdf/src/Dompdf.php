<?php
namespace Vsmoraes\Pdf;
use Illuminate\Http\Response;
class Dompdf implements Pdf
{
    protected $dompdfInstance;
    protected $filename = 'dompdf_out.pdf';
    public function __construct(\DOMPDF $dompdf)
    {
        $this->dompdfInstance = $dompdf;
    }
    public function load($html, $size = 'A4', $orientation = 'portrait')
    {
        $this->dompdfInstance->load_html($html);
        $this->setPaper($size, $orientation);
        return $this;
    }
    public function filename($filename = null)
    {
        if ($filename) {
            $this->filename = $filename;
            return $this;
        }
        return $this->filename;
    }
    public function setPaper($size, $orientation)
    {
        return $this->dompdfInstance->set_paper($size, $orientation);
    }
    public function render()
    {
        return $this->dompdfInstance->render();
    }
    public function clear()
    {
        \Image_Cache::clear();
        return true;
    }
    public function show($options = ['compress' => 1, 'Attachment' => 0])
    {
        $this->render();
        $this->clear();
        return $this->dompdfInstance->stream($this->filename(), $options);
    }
    public function download($options = ['compress' => 1, 'Attachment' => 1])
    {
        return new Response($this->show($options), 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
    public function output($options = ['compress' => 1])
    {
        $this->render();
        return $this->dompdfInstance->output($options);
    }
}
