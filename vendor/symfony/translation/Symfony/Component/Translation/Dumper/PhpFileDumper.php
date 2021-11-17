<?php
namespace Symfony\Component\Translation\Dumper;
use Symfony\Component\Translation\MessageCatalogue;
class PhpFileDumper extends FileDumper
{
    protected function format(MessageCatalogue $messages, $domain)
    {
        $output = "<?php\n\nreturn ".var_export($messages->all($domain), true).";\n";
        return $output;
    }
    protected function getExtension()
    {
        return 'php';
    }
}
