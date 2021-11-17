<?php
interface Swift_Mime_HeaderFactory extends Swift_Mime_CharsetObserver
{
    public function createMailboxHeader($name, $addresses = null);
    public function createDateHeader($name, $timestamp = null);
    public function createTextHeader($name, $value = null);
    public function createParameterizedHeader($name, $value = null, $params = array());
    public function createIdHeader($name, $ids = null);
    public function createPathHeader($name, $path = null);
}
