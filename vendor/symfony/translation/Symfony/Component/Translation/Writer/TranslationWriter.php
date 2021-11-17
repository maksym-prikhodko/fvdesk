<?php
namespace Symfony\Component\Translation\Writer;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Dumper\DumperInterface;
class TranslationWriter
{
    private $dumpers = array();
    public function addDumper($format, DumperInterface $dumper)
    {
        $this->dumpers[$format] = $dumper;
    }
    public function disableBackup()
    {
        foreach ($this->dumpers as $dumper) {
            $dumper->setBackup(false);
        }
    }
    public function getFormats()
    {
        return array_keys($this->dumpers);
    }
    public function writeTranslations(MessageCatalogue $catalogue, $format, $options = array())
    {
        if (!isset($this->dumpers[$format])) {
            throw new \InvalidArgumentException(sprintf('There is no dumper associated with format "%s".', $format));
        }
        $dumper = $this->dumpers[$format];
        if (isset($options['path']) && !is_dir($options['path'])) {
            mkdir($options['path'], 0777, true);
        }
        $dumper->dump($catalogue, $options);
    }
}
