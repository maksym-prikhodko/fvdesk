<?php
namespace Symfony\Component\Translation\Loader;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Config\Resource\FileResource;
class XliffFileLoader implements LoaderInterface
{
    public function load($resource, $locale, $domain = 'messages')
    {
        if (!stream_is_local($resource)) {
            throw new InvalidResourceException(sprintf('This is not a local file "%s".', $resource));
        }
        if (!file_exists($resource)) {
            throw new NotFoundResourceException(sprintf('File "%s" not found.', $resource));
        }
        list($xml, $encoding) = $this->parseFile($resource);
        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:1.2');
        $catalogue = new MessageCatalogue($locale);
        foreach ($xml->xpath('
            $attributes = $translation->attributes();
            if (!(isset($attributes['resname']) || isset($translation->source)) || !isset($translation->target)) {
                continue;
            }
            $source = isset($attributes['resname']) && $attributes['resname'] ? $attributes['resname'] : $translation->source;
            $target = $this->utf8ToCharset((string) $translation->target, $encoding);
            $catalogue->set((string) $source, $target, $domain);
            if (isset($translation->note)) {
                $notes = array();
                foreach ($translation->note as $xmlNote) {
                    $noteAttributes = $xmlNote->attributes();
                    $note = array('content' => $this->utf8ToCharset((string) $xmlNote, $encoding));
                    if (isset($noteAttributes['priority'])) {
                        $note['priority'] = (int) $noteAttributes['priority'];
                    }
                    if (isset($noteAttributes['from'])) {
                        $note['from'] = (string) $noteAttributes['from'];
                    }
                    $notes[] = $note;
                }
                $catalogue->setMetadata((string) $source, array('notes' => $notes), $domain);
            }
        }
        $catalogue->addResource(new FileResource($resource));
        return $catalogue;
    }
    private function utf8ToCharset($content, $encoding = null)
    {
        if ('UTF-8' !== $encoding && !empty($encoding)) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($content, $encoding, 'UTF-8');
            }
            if (function_exists('iconv')) {
                return iconv('UTF-8', $encoding, $content);
            }
            throw new \RuntimeException('No suitable convert encoding function (use UTF-8 as your encoding or install the iconv or mbstring extension).');
        }
        return $content;
    }
    private function parseFile($file)
    {
        try {
            $dom = XmlUtils::loadFile($file);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidResourceException(sprintf('Unable to load "%s": %s', $file, $e->getMessage()), $e->getCode(), $e);
        }
        $internalErrors = libxml_use_internal_errors(true);
        $location = str_replace('\\', '/', __DIR__).'/schema/dic/xliff-core/xml.xsd';
        $parts = explode('/', $location);
        if (0 === stripos($location, 'phar:
            $tmpfile = tempnam(sys_get_temp_dir(), 'sf2');
            if ($tmpfile) {
                copy($location, $tmpfile);
                $parts = explode('/', str_replace('\\', '/', $tmpfile));
            }
        }
        $drive = '\\' === DIRECTORY_SEPARATOR ? array_shift($parts).'/' : '';
        $location = 'file:
        $source = file_get_contents(__DIR__.'/schema/dic/xliff-core/xliff-core-1.2-strict.xsd');
        $source = str_replace('http:
        if (!@$dom->schemaValidateSource($source)) {
            throw new InvalidResourceException(implode("\n", $this->getXmlErrors($internalErrors)));
        }
        $dom->normalizeDocument();
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);
        return array(simplexml_import_dom($dom), strtoupper($dom->encoding));
    }
    private function getXmlErrors($internalErrors)
    {
        $errors = array();
        foreach (libxml_get_errors() as $error) {
            $errors[] = sprintf('[%s %s] %s (in %s - line %d, column %d)',
                LIBXML_ERR_WARNING == $error->level ? 'WARNING' : 'ERROR',
                $error->code,
                trim($error->message),
                $error->file ?: 'n/a',
                $error->line,
                $error->column
            );
        }
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);
        return $errors;
    }
}