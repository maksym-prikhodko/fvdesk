<?php
namespace Psy\Presenter;
use Psy\Util\Json;
class ArrayPresenter extends RecursivePresenter
{
    const ARRAY_OBJECT_FMT = '<object>\\<<class>%s</class> <strong>#%s</strong>></object>';
    public function canPresent($value)
    {
        return is_array($value) || $this->isArrayObject($value);
    }
    protected function isArrayObject($value)
    {
        return $value instanceof \ArrayObject;
    }
    public function presentRef($value)
    {
        if ($this->isArrayObject($value)) {
            return $this->presentArrayObjectRef($value);
        } elseif (empty($value)) {
            return '[]';
        } else {
            return sprintf('Array(<number>%d</number>)', count($value));
        }
    }
    protected function presentArrayObjectRef($value)
    {
        return sprintf(self::ARRAY_OBJECT_FMT, get_class($value), spl_object_hash($value));
    }
    protected function getArrayObjectValue($value)
    {
        return iterator_to_array($value->getIterator());
    }
    protected function presentValue($value, $depth = null, $options = 0)
    {
        $prefix = '';
        if ($this->isArrayObject($value)) {
            $prefix = $this->presentArrayObjectRef($value) . ' ';
            $value  = $this->getArrayObjectValue($value);
        }
        if (empty($value) || $depth === 0) {
            return $prefix . $this->presentRef($value);
        }
        $formatted = array();
        foreach ($value as $key => $val) {
            $formatted[$key] = $this->presentSubValue($val);
        }
        if ($this->shouldShowKeys($value)) {
            $pad = max(array_map('strlen', array_map(array('Psy\Util\Json', 'encode'), array_keys($value))));
            foreach ($formatted as $key => $val) {
                $formatted[$key] = $this->formatKeyAndValue($key, $val, $pad);
            }
        } else {
            $formatted = array_map(array($this, 'indentValue'), $formatted);
        }
        $template = sprintf('%s[%s%s%%s%s]', $prefix, PHP_EOL, self::INDENT, PHP_EOL);
        $glue     = sprintf(',%s%s', PHP_EOL, self::INDENT);
        return sprintf($template, implode($glue, $formatted));
    }
    protected function shouldShowKeys(array $array)
    {
        $i = 0;
        foreach (array_keys($array) as $k) {
            if ($k !== $i++) {
                return true;
            }
        }
        return false;
    }
    protected function formatKeyAndValue($key, $value, $pad = 0)
    {
        $type = is_string($value) ? 'string' : 'number';
        $tpl  = "<$type>%-${pad}s</$type> => %s";
        return sprintf(
            $tpl,
            Json::encode($key),
            $this->indentValue($value)
        );
    }
}
