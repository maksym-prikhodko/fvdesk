<?php
namespace Psy\Presenter;
class ResourcePresenter extends RecursivePresenter
{
    const FMT = '<resource>\\<%s <strong>resource #%s</strong>></resource>';
    public function canPresent($value)
    {
        return is_resource($value);
    }
    public function presentRef($value)
    {
        $type = get_resource_type($value);
        if ($type === 'stream') {
            $meta = stream_get_meta_data($value);
            $type = sprintf('%s stream', $meta['stream_type']);
        }
        $id = str_replace('Resource id #', '', (string) $value);
        return sprintf(self::FMT, $type, $id);
    }
    public function presentValue($value, $depth = null, $options = 0)
    {
        if ($depth === 0 || !($options & Presenter::VERBOSE)) {
            return $this->presentRef($value);
        }
        return sprintf('%s %s', $this->presentRef($value), $this->formatMetadata($value));
    }
    protected function formatMetadata($value)
    {
        $props = array();
        switch (get_resource_type($value)) {
            case 'stream':
                $props = stream_get_meta_data($value);
                break;
            case 'curl':
                $props = curl_getinfo($value);
                break;
            case 'xml':
                $props = array(
                    'current_byte_index'    => xml_get_current_byte_index($value),
                    'current_column_number' => xml_get_current_column_number($value),
                    'current_line_number'   => xml_get_current_line_number($value),
                    'error_code'            => xml_get_error_code($value),
                );
                break;
        }
        if (empty($props)) {
            return '{}';
        }
        $formatted = array();
        foreach ($props as $name => $value) {
            $formatted[] = sprintf('%s: %s', $name, $this->indentValue($this->presentSubValue($value)));
        }
        $template = sprintf('{%s%s%%s%s}', PHP_EOL, self::INDENT, PHP_EOL);
        $glue     = sprintf(',%s%s', PHP_EOL, self::INDENT);
        return sprintf($template, implode($glue, $formatted));
    }
}
