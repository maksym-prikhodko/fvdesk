<?php
namespace Monolog\Handler;
use Monolog\Logger;
class NewRelicHandler extends AbstractProcessingHandler
{
    protected $appName;
    protected $transactionName;
    protected $explodeArrays;
    public function __construct(
        $level = Logger::ERROR,
        $bubble = true,
        $appName = null,
        $explodeArrays = false,
        $transactionName = null
    ) {
        parent::__construct($level, $bubble);
        $this->appName       = $appName;
        $this->explodeArrays = $explodeArrays;
        $this->transactionName = $transactionName;
    }
    protected function write(array $record)
    {
        if (!$this->isNewRelicEnabled()) {
            throw new MissingExtensionException('The newrelic PHP extension is required to use the NewRelicHandler');
        }
        if ($appName = $this->getAppName($record['context'])) {
            $this->setNewRelicAppName($appName);
        }
        if ($transactionName = $this->getTransactionName($record['context'])) {
            $this->setNewRelicTransactionName($transactionName);
            unset($record['context']['transaction_name']);
        }
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Exception) {
            newrelic_notice_error($record['message'], $record['context']['exception']);
            unset($record['context']['exception']);
        } else {
            newrelic_notice_error($record['message']);
        }
        foreach ($record['context'] as $key => $parameter) {
            if (is_array($parameter) && $this->explodeArrays) {
                foreach ($parameter as $paramKey => $paramValue) {
                    newrelic_add_custom_parameter('context_' . $key . '_' . $paramKey, $paramValue);
                }
            } else {
                newrelic_add_custom_parameter('context_' . $key, $parameter);
            }
        }
        foreach ($record['extra'] as $key => $parameter) {
            if (is_array($parameter) && $this->explodeArrays) {
                foreach ($parameter as $paramKey => $paramValue) {
                    newrelic_add_custom_parameter('extra_' . $key . '_' . $paramKey, $paramValue);
                }
            } else {
                newrelic_add_custom_parameter('extra_' . $key, $parameter);
            }
        }
    }
    protected function isNewRelicEnabled()
    {
        return extension_loaded('newrelic');
    }
    protected function getAppName(array $context)
    {
        if (isset($context['appname'])) {
            return $context['appname'];
        }
        return $this->appName;
    }
    protected function getTransactionName(array $context)
    {
        if (isset($context['transaction_name'])) {
            return $context['transaction_name'];
        }
        return $this->transactionName;
    }
    protected function setNewRelicAppName($appName)
    {
        newrelic_set_appname($appName);
    }
    protected function setNewRelicTransactionName($transactionName)
    {
        newrelic_name_transaction($transactionName);
    }
}
