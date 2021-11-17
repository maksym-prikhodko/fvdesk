<?php
namespace Monolog\Processor;
class MemoryUsageProcessor extends MemoryProcessor
{
    public function __invoke(array $record)
    {
        $bytes = memory_get_usage($this->realUsage);
        $formatted = $this->formatBytes($bytes);
        $record['extra'] = array_merge(
            $record['extra'],
            array(
                'memory_usage' => $formatted,
            )
        );
        return $record;
    }
}
