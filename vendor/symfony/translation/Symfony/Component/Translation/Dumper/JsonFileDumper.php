<?php
namespace Symfony\Component\Translation\Dumper;
use Symfony\Component\Translation\MessageCatalogue;
if (!defined('JSON_PRETTY_PRINT')) {
    define('JSON_PRETTY_PRINT', 128);
}
class JsonFileDumper extends FileDumper
{
    public function format(MessageCatalogue $messages, $domain = 'messages')
    {
        return json_encode($messages->all($domain), JSON_PRETTY_PRINT);
    }
    protected function getExtension()
    {
        return 'json';
    }
}
