<?php
namespace Monolog\Processor;
use Monolog\Logger;
class IntrospectionProcessor
{
    private $level;
    private $skipClassesPartials;
    public function __construct($level = Logger::DEBUG, array $skipClassesPartials = array('Monolog\\'))
    {
        $this->level = Logger::toMonologLevel($level);
        $this->skipClassesPartials = $skipClassesPartials;
    }
    public function __invoke(array $record)
    {
        if ($record['level'] < $this->level) {
            return $record;
        }
        $trace = debug_backtrace();
        array_shift($trace);
        array_shift($trace);
        $i = 0;
        while (isset($trace[$i]['class'])) {
            foreach ($this->skipClassesPartials as $part) {
                if (strpos($trace[$i]['class'], $part) !== false) {
                    $i++;
                    continue 2;
                }
            }
            break;
        }
        $record['extra'] = array_merge(
            $record['extra'],
            array(
                'file'      => isset($trace[$i-1]['file']) ? $trace[$i-1]['file'] : null,
                'line'      => isset($trace[$i-1]['line']) ? $trace[$i-1]['line'] : null,
                'class'     => isset($trace[$i]['class']) ? $trace[$i]['class'] : null,
                'function'  => isset($trace[$i]['function']) ? $trace[$i]['function'] : null,
            )
        );
        return $record;
    }
}
