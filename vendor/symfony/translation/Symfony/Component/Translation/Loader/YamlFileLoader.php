<?php
namespace Symfony\Component\Translation\Loader;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Exception\ParseException;
class YamlFileLoader extends ArrayLoader
{
    private $yamlParser;
    public function load($resource, $locale, $domain = 'messages')
    {
        if (!stream_is_local($resource)) {
            throw new InvalidResourceException(sprintf('This is not a local file "%s".', $resource));
        }
        if (!file_exists($resource)) {
            throw new NotFoundResourceException(sprintf('File "%s" not found.', $resource));
        }
        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }
        try {
            $messages = $this->yamlParser->parse(file_get_contents($resource));
        } catch (ParseException $e) {
            throw new InvalidResourceException(sprintf('Error parsing YAML, invalid file "%s"', $resource), 0, $e);
        }
        if (null === $messages) {
            $messages = array();
        }
        if (!is_array($messages)) {
            throw new InvalidResourceException(sprintf('The file "%s" must contain a YAML array.', $resource));
        }
        $catalogue = parent::load($messages, $locale, $domain);
        $catalogue->addResource(new FileResource($resource));
        return $catalogue;
    }
}
