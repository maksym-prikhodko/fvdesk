<?php
namespace Prophecy\Util;
use Prophecy\Prophecy\ProphecyInterface;
use SplObjectStorage;
class ExportUtil
{
    public static function export($value, $indentation = 0)
    {
        return static::recursiveExport($value, $indentation);
    }
    public static function toArray($object)
    {
        $array = array();
        foreach ((array) $object as $key => $value) {
            if (preg_match('/^\0.+\0(.+)$/', $key, $matches)) {
                $key = $matches[1];
            }
            $array[$key] = $value;
        }
        if ($object instanceof SplObjectStorage) {
            foreach ($object as $key => $value) {
                $array[spl_object_hash($value)] = array(
                    'obj' => $value,
                    'inf' => $object->getInfo(),
                );
            }
        }
        return $array;
    }
    protected static function recursiveExport($value, $indentation, &$processedObjects = array())
    {
        if ($value === null) {
            return 'null';
        }
        if ($value === true) {
            return 'true';
        }
        if ($value === false) {
            return 'false';
        }
        if (is_string($value)) {
            if (preg_match('/[^\x09-\x0d\x20-\xff]/', $value)) {
                return 'Binary String: 0x' . bin2hex($value);
            }
            return "'" . str_replace(array("\r\n", "\n\r", "\r"), array("\n", "\n", "\n"), $value) . "'";
        }
        $origValue = $value;
        if (is_object($value)) {
            if ($value instanceof ProphecyInterface) {
                return sprintf('%s Object (*Prophecy*)', get_class($value));
            } elseif (in_array($value, $processedObjects, true)) {
                return sprintf('%s Object (*RECURSION*)', get_class($value));
            }
            $processedObjects[] = $value;
            $value = self::toArray($value);
        }
        if (is_array($value)) {
            $whitespace = str_repeat('    ', $indentation);
            preg_match_all('/\n            \[(\w+)\] => Array\s+\*RECURSION\*/', print_r($value, true), $matches);
            $recursiveKeys = array_unique($matches[1]);
            foreach ($recursiveKeys as $key => $recursiveKey) {
                if ((string) (integer) $recursiveKey === $recursiveKey) {
                    $recursiveKeys[$key] = (integer) $recursiveKey;
                }
            }
            $content = '';
            foreach ($value as $key => $val) {
                if (in_array($key, $recursiveKeys, true)) {
                    $val = 'Array (*RECURSION*)';
                } else {
                    $val = self::recursiveExport($val, $indentation + 1, $processedObjects);
                }
                $content .= $whitespace . '    ' . self::export($key) . ' => ' . $val . "\n";
            }
            if (strlen($content) > 0) {
                $content = "\n" . $content . $whitespace;
            }
            return sprintf(
                "%s (%s)",
                is_object($origValue) ? sprintf('%s:%s', get_class($origValue), spl_object_hash($origValue)) . ' Object' : 'Array', $content
            );
        }
        if (is_double($value) && (double)(integer) $value === $value) {
            return $value . '.0';
        }
        return (string) $value;
    }
}
