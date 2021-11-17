<?php
namespace Vsmoraes\Pdf;
use Illuminate\Support\Facades\Facade;
class PdfFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Vsmoraes\Pdf\Pdf';
    }
}
