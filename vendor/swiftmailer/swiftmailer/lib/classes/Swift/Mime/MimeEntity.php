<?php
interface Swift_Mime_MimeEntity extends Swift_Mime_CharsetObserver, Swift_Mime_EncodingObserver
{
    const LEVEL_TOP = 16;
    const LEVEL_MIXED = 256;
    const LEVEL_ALTERNATIVE = 4096;
    const LEVEL_RELATED = 65536;
    public function getNestingLevel();
    public function getContentType();
    public function getId();
    public function getChildren();
    public function setChildren(array $children);
    public function getHeaders();
    public function getBody();
    public function setBody($body, $contentType = null);
    public function toString();
    public function toByteStream(Swift_InputByteStream $is);
}
