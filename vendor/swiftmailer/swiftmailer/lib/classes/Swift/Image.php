<?php
class Swift_Image extends Swift_EmbeddedFile
{
    public function __construct($data = null, $filename = null, $contentType = null)
    {
        parent::__construct($data, $filename, $contentType);
    }
    public static function newInstance($data = null, $filename = null, $contentType = null)
    {
        return new self($data, $filename, $contentType);
    }
    public static function fromPath($path)
    {
        $image = self::newInstance()->setFile(
            new Swift_ByteStream_FileByteStream($path)
            );
        return $image;
    }
}
