<?php
namespace Monolog\Handler;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use Raven_Client;
class RavenHandler extends AbstractProcessingHandler
{
    private $logLevels = array(
        Logger::DEBUG     => Raven_Client::DEBUG,
        Logger::INFO      => Raven_Client::INFO,
        Logger::NOTICE    => Raven_Client::INFO,
        Logger::WARNING   => Raven_Client::WARNING,
        Logger::ERROR     => Raven_Client::ERROR,
        Logger::CRITICAL  => Raven_Client::FATAL,
        Logger::ALERT     => Raven_Client::FATAL,
        Logger::EMERGENCY => Raven_Client::FATAL,
    );
    protected $ravenClient;
    protected $batchFormatter;
    public function __construct(Raven_Client $ravenClient, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->ravenClient = $ravenClient;
    }
    public function handleBatch(array $records)
    {
        $level = $this->level;
        $records = array_filter($records, function ($record) use ($level) {
            return $record['level'] >= $level;
        });
        if (!$records) {
            return;
        }
        $record = array_reduce($records, function ($highest, $record) {
            if ($record['level'] >= $highest['level']) {
                return $record;
            }
            return $highest;
        });
        $logs = array();
        foreach ($records as $r) {
            $logs[] = $this->processRecord($r);
        }
        if ($logs) {
            $record['context']['logs'] = (string) $this->getBatchFormatter()->formatBatch($logs);
        }
        $this->handle($record);
    }
    public function setBatchFormatter(FormatterInterface $formatter)
    {
        $this->batchFormatter = $formatter;
    }
    public function getBatchFormatter()
    {
        if (!$this->batchFormatter) {
            $this->batchFormatter = $this->getDefaultBatchFormatter();
        }
        return $this->batchFormatter;
    }
    protected function write(array $record)
    {
        $this->ravenClient->user_context(null);
        $options = array();
        $options['level'] = $this->logLevels[$record['level']];
        $options['tags'] = array();
        if (!empty($record['extra']['tags'])) {
            $options['tags'] = array_merge($options['tags'], $record['extra']['tags']);
            unset($record['extra']['tags']);
        }
        if (!empty($record['context']['tags'])) {
            $options['tags'] = array_merge($options['tags'], $record['context']['tags']);
            unset($record['context']['tags']);
        }
        if (!empty($record['context']['logger'])) {
            $options['logger'] = $record['context']['logger'];
            unset($record['context']['logger']);
        } else {
            $options['logger'] = $record['channel'];
        }
        if (!empty($record['context'])) {
            $options['extra']['context'] = $record['context'];
            if (!empty($record['context']['user'])) {
                $this->ravenClient->user_context($record['context']['user']);
                unset($options['extra']['context']['user']);
            }
        }
        if (!empty($record['extra'])) {
            $options['extra']['extra'] = $record['extra'];
        }
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Exception) {
            $options['extra']['message'] = $record['formatted'];
            $this->ravenClient->captureException($record['context']['exception'], $options);
            return;
        }
        $this->ravenClient->captureMessage($record['formatted'], array(), $options);
    }
    protected function getDefaultFormatter()
    {
        return new LineFormatter('[%channel%] %message%');
    }
    protected function getDefaultBatchFormatter()
    {
        return new LineFormatter();
    }
}
