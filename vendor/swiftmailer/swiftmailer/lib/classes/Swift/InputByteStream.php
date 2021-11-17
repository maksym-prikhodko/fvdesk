<?php
interface Swift_InputByteStream
{
    public function write($bytes);
    public function commit();
    public function bind(Swift_InputByteStream $is);
    public function unbind(Swift_InputByteStream $is);
    public function flushBuffers();
}
