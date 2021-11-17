<?php
namespace Monolog\Handler;
use RollbarNotifier;
use Exception;
use Monolog\Logger;
class RollbarHandler extends AbstractProcessingHandler
{
    protected $rollbarNotifier;
    public function __construct(RollbarNotifier $rollbarNotifier, $level = Logger::ERROR, $bubble = true)
    {
        $this->rollbarNotifier = $rollbarNotifier;
        parent::__construct($level, $bubble);
    }
    protected function write(array $record)
    {
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof Exception) {
            $this->rollbarNotifier->report_exception($record['context']['exception']);
        } else {
            $extraData = array(
                'level' => $record['level'],
                'channel' => $record['channel'],
                'datetime' => $record['datetime']->format('U'),
            );
            $this->rollbarNotifier->report_message(
                $record['message'],
                $record['level_name'],
                array_merge($record['context'], $record['extra'], $extraData)
            );
        }
    }
    public function close()
    {
        $this->rollbarNotifier->flush();
    }
}
