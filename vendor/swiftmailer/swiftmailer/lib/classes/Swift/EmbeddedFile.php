<?php
class Swift_EmbeddedFile extends Swift_Mime_EmbeddedFile
{
    public function __construct($data = null, $filename = null, $contentType = null)
    {
        call_user_func_array(
            array($this, 'Swift_Mime_EmbeddedFile::__construct'),
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('mime.embeddedfile')
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
    public static function fromPath($path)
    {
        return self::newInstance()->setFile(
            new Swift_ByteStream_FileByteStream($path)
            );
    }
}
