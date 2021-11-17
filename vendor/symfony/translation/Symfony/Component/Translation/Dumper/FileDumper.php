<?php
namespace Symfony\Component\Translation\Dumper;
use Symfony\Component\Translation\MessageCatalogue;
abstract class FileDumper implements DumperInterface
{
    protected $relativePathTemplate = '%domain%.%locale%.%extension%';
    private $backup = true;
    public function setRelativePathTemplate($relativePathTemplate)
    {
        $this->relativePathTemplate = $relativePathTemplate;
    }
    public function setBackup($backup)
    {
        $this->backup = $backup;
    }
    public function dump(MessageCatalogue $messages, $options = array())
    {
        if (!array_key_exists('path', $options)) {
            throw new \InvalidArgumentException('The file dumper needs a path option.');
        }
        foreach ($messages->getDomains() as $domain) {
            $fullpath = $options['path'].'/'.$this->getRelativePath($domain, $messages->getLocale());
            if (file_exists($fullpath)) {
                if ($this->backup) {
                    copy($fullpath, $fullpath.'~');
                }
            } else {
                $directory = dirname($fullpath);
                if (!file_exists($directory) && !@mkdir($directory, 0777, true)) {
                    throw new \RuntimeException(sprintf('Unable to create directory "%s".', $directory));
                }
            }
            file_put_contents($fullpath, $this->format($messages, $domain));
        }
    }
    abstract protected function format(MessageCatalogue $messages, $domain);
    abstract protected function getExtension();
    private function getRelativePath($domain, $locale)
    {
        return strtr($this->relativePathTemplate, array(
            '%domain%' => $domain,
            '%locale%' => $locale,
            '%extension%' => $this->getExtension(),
        ));
    }
}
