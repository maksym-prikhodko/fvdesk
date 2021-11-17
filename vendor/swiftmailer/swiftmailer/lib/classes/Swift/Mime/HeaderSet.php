<?php
interface Swift_Mime_HeaderSet extends Swift_Mime_CharsetObserver
{
    public function addMailboxHeader($name, $addresses = null);
    public function addDateHeader($name, $timestamp = null);
    public function addTextHeader($name, $value = null);
    public function addParameterizedHeader($name, $value = null, $params = array());
    public function addIdHeader($name, $ids = null);
    public function addPathHeader($name, $path = null);
    public function has($name, $index = 0);
    public function set(Swift_Mime_Header $header, $index = 0);
    public function get($name, $index = 0);
    public function getAll($name = null);
    public function listAll();
    public function remove($name, $index = 0);
    public function removeAll($name);
    public function newInstance();
    public function defineOrdering(array $sequence);
    public function setAlwaysDisplayed(array $names);
    public function toString();
}
