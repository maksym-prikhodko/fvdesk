<?php
namespace Symfony\Component\Translation\Loader;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Config\Resource\FileResource;
class PhpFileLoader extends ArrayLoader
{
    public function load($resource, $locale, $domain = 'messages')
    {
        if (!stream_is_local($resource)) {
            throw new InvalidResourceException(sprintf('This is not a local file "%s".', $resource));
        }
        if (!file_exists($resource)) {
            throw new NotFoundResourceException(sprintf('File "%s" not found.', $resource));
        }
        $messages = require $resource;
        $catalogue = parent::load($messages, $locale, $domain);
        $catalogue->addResource(new FileResource($resource));
        return $catalogue;
    }
}
