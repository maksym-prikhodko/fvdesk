<?php
namespace Symfony\Component\Translation\Loader;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Config\Resource\FileResource;
class PoFileLoader extends ArrayLoader
{
    public function load($resource, $locale, $domain = 'messages')
    {
        if (!stream_is_local($resource)) {
            throw new InvalidResourceException(sprintf('This is not a local file "%s".', $resource));
        }
        if (!file_exists($resource)) {
            throw new NotFoundResourceException(sprintf('File "%s" not found.', $resource));
        }
        $messages = $this->parse($resource);
        if (null === $messages) {
            $messages = array();
        }
        if (!is_array($messages)) {
            throw new InvalidResourceException(sprintf('The file "%s" must contain a valid po file.', $resource));
        }
        $catalogue = parent::load($messages, $locale, $domain);
        $catalogue->addResource(new FileResource($resource));
        return $catalogue;
    }
    private function parse($resource)
    {
        $stream = fopen($resource, 'r');
        $defaults = array(
            'ids' => array(),
            'translated' => null,
        );
        $messages = array();
        $item = $defaults;
        while ($line = fgets($stream)) {
            $line = trim($line);
            if ($line === '') {
                $this->addMessage($messages, $item);
                $item = $defaults;
            } elseif (substr($line, 0, 7) === 'msgid "') {
                $this->addMessage($messages, $item);
                $item = $defaults;
                $item['ids']['singular'] = substr($line, 7, -1);
            } elseif (substr($line, 0, 8) === 'msgstr "') {
                $item['translated'] = substr($line, 8, -1);
            } elseif ($line[0] === '"') {
                $continues = isset($item['translated']) ? 'translated' : 'ids';
                if (is_array($item[$continues])) {
                    end($item[$continues]);
                    $item[$continues][key($item[$continues])] .= substr($line, 1, -1);
                } else {
                    $item[$continues] .= substr($line, 1, -1);
                }
            } elseif (substr($line, 0, 14) === 'msgid_plural "') {
                $item['ids']['plural'] = substr($line, 14, -1);
            } elseif (substr($line, 0, 7) === 'msgstr[') {
                $size = strpos($line, ']');
                $item['translated'][(int) substr($line, 7, 1)] = substr($line, $size + 3, -1);
            }
        }
        $this->addMessage($messages, $item);
        fclose($stream);
        return $messages;
    }
    private function addMessage(array &$messages, array $item)
    {
        if (is_array($item['translated'])) {
            $messages[stripcslashes($item['ids']['singular'])] = stripcslashes($item['translated'][0]);
            if (isset($item['ids']['plural'])) {
                $plurals = $item['translated'];
                ksort($plurals);
                end($plurals);
                $count = key($plurals);
                $empties = array_fill(0, $count + 1, '-');
                $plurals += $empties;
                ksort($plurals);
                $messages[stripcslashes($item['ids']['plural'])] = stripcslashes(implode('|', $plurals));
            }
        } elseif (!empty($item['ids']['singular'])) {
            $messages[stripcslashes($item['ids']['singular'])] = stripcslashes($item['translated']);
        }
    }
}
