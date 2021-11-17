<?php
namespace Monolog\Formatter;
class JsonFormatter implements FormatterInterface
{
    const BATCH_MODE_JSON = 1;
    const BATCH_MODE_NEWLINES = 2;
    protected $batchMode;
    protected $appendNewline;
    public function __construct($batchMode = self::BATCH_MODE_JSON, $appendNewline = true)
    {
        $this->batchMode = $batchMode;
        $this->appendNewline = $appendNewline;
    }
    public function getBatchMode()
    {
        return $this->batchMode;
    }
    public function isAppendingNewlines()
    {
        return $this->appendNewline;
    }
    public function format(array $record)
    {
        return json_encode($record) . ($this->appendNewline ? "\n" : '');
    }
    public function formatBatch(array $records)
    {
        switch ($this->batchMode) {
            case static::BATCH_MODE_NEWLINES:
                return $this->formatBatchNewlines($records);
            case static::BATCH_MODE_JSON:
            default:
                return $this->formatBatchJson($records);
        }
    }
    protected function formatBatchJson(array $records)
    {
        return json_encode($records);
    }
    protected function formatBatchNewlines(array $records)
    {
        $instance = $this;
        $oldNewline = $this->appendNewline;
        $this->appendNewline = false;
        array_walk($records, function (&$value, $key) use ($instance) {
            $value = $instance->format($value);
        });
        $this->appendNewline = $oldNewline;
        return implode("\n", $records);
    }
}
