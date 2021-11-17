<?php
namespace Symfony\Component\HttpKernel\DataCollector\Util;
class ValueExporter
{
    public function exportValue($value, $depth = 1, $deep = false)
    {
        if (is_object($value)) {
            if ($value instanceof \DateTime || $value instanceof \DateTimeInterface) {
                return sprintf('Object(%s) - %s', get_class($value), $value->format(\DateTime::ISO8601));
            }
            return sprintf('Object(%s)', get_class($value));
        }
        if (is_array($value)) {
            if (empty($value)) {
                return '[]';
            }
            $indent = str_repeat('  ', $depth);
            $a = array();
            foreach ($value as $k => $v) {
                if (is_array($v)) {
                    $deep = true;
                }
                $a[] = sprintf('%s => %s', $k, $this->exportValue($v, $depth + 1, $deep));
            }
            if ($deep) {
                return sprintf("[\n%s%s\n%s]", $indent, implode(sprintf(", \n%s", $indent), $a), str_repeat('  ', $depth - 1));
            }
            return sprintf("[%s]", implode(', ', $a));
        }
        if (is_resource($value)) {
            return sprintf('Resource(%s#%d)', get_resource_type($value), $value);
        }
        if (null === $value) {
            return 'null';
        }
        if (false === $value) {
            return 'false';
        }
        if (true === $value) {
            return 'true';
        }
        return (string) $value;
    }
}
