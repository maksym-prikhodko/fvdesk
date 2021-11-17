<?php
namespace Symfony\Component\Translation\Loader;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Config\Resource\FileResource;
class CsvFileLoader extends ArrayLoader
{
    private $delimiter = ';';
    private $enclosure = '"';
    private $escape = '\\';
    public function load($resource, $locale, $domain = 'messages')
    {
        if (!stream_is_local($resource)) {
            throw new InvalidResourceException(sprintf('This is not a local file "%s".', $resource));
        }
        if (!file_exists($resource)) {
            throw new NotFoundResourceException(sprintf('File "%s" not found.', $resource));
        }
        $messages = array();
        try {
            $file = new \SplFileObject($resource, 'rb');
        } catch (\RuntimeException $e) {
            throw new NotFoundResourceException(sprintf('Error opening file "%s".', $resource), 0, $e);
        }
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);
        $file->setCsvControl($this->delimiter, $this->enclosure, $this->escape);
        foreach ($file as $data) {
            if (substr($data[0], 0, 1) === '#') {
                continue;
            }
            if (!isset($data[1])) {
                continue;
            }
            if (count($data) == 2) {
                $messages[$data[0]] = $data[1];
            } else {
                continue;
            }
        }
        $catalogue = parent::load($messages, $locale, $domain);
        $catalogue->addResource(new FileResource($resource));
        return $catalogue;
    }
    public function setCsvControl($delimiter = ';', $enclosure = '"', $escape = '\\')
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
    }
}
