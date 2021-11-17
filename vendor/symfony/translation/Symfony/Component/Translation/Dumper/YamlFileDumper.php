<?php
namespace Symfony\Component\Translation\Dumper;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Yaml\Yaml;
class YamlFileDumper extends FileDumper
{
    protected function format(MessageCatalogue $messages, $domain)
    {
        return Yaml::dump($messages->all($domain));
    }
    protected function getExtension()
    {
        return 'yml';
    }
}
