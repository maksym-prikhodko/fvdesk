<?php
class Swift_Encoding
{
    public static function get7BitEncoding()
    {
        return self::_lookup('mime.7bitcontentencoder');
    }
    public static function get8BitEncoding()
    {
        return self::_lookup('mime.8bitcontentencoder');
    }
    public static function getQpEncoding()
    {
        return self::_lookup('mime.qpcontentencoder');
    }
    public static function getBase64Encoding()
    {
        return self::_lookup('mime.base64contentencoder');
    }
    private static function _lookup($key)
    {
        return Swift_DependencyContainer::getInstance()->lookup($key);
    }
}
