<?php
class Swift_Attachment extends Swift_Mime_Attachment
{
    public function __construct($data = null, $filename = null, $contentType = null)
    {
        call_user_func_array(
            array($this, 'Swift_Mime_Attachment::__construct'),
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('mime.attachment')
            );
        $this->setBody($data);
        $this->setFilename($filename);
        if ($contentType) {
            $this->setContentType($contentType);
        }
    }
    public static function newInstance($data = null, $filename = null, $contentType = null)
    {
        return new self($data, $filename, $contentType);
    }
    public static function fromPath($path, $contentType = null)
    {
        return self::newInstance()->setFile(
            new Swift_ByteStream_FileByteStream($path),
            $contentType
            );
    }
}
