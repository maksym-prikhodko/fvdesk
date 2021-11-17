<?php
class Bugsnag_Client
{
    private $config;
    private $notification;
    public function __construct($apiKey)
    {
        if (!is_string($apiKey)) {
            throw new Exception('Bugsnag Error: Invalid API key');
        }
        $this->config = new Bugsnag_Configuration();
        $this->config->apiKey = $apiKey;
        $this->diagnostics = new Bugsnag_Diagnostics($this->config);
        register_shutdown_function(array($this, 'shutdownHandler'));
    }
    public function setReleaseStage($releaseStage)
    {
        $this->config->releaseStage = $releaseStage;
        return $this;
    }
    public function setAppVersion($appVersion)
    {
        $this->config->appVersion = $appVersion;
        return $this;
    }
    public function setHostname($hostname)
    {
        $this->config->hostname = $hostname;
        return $this;
    }
    public function setNotifyReleaseStages(array $notifyReleaseStages)
    {
        $this->config->notifyReleaseStages = $notifyReleaseStages;
        return $this;
    }
    public function setEndpoint($endpoint)
    {
        $this->config->endpoint = $endpoint;
        return $this;
    }
    public function setUseSSL($useSSL)
    {
        $this->config->useSSL = $useSSL;
        return $this;
    }
    public function setTimeout($timeout)
    {
        $this->config->timeout = $timeout;
        return $this;
    }
    public function setProjectRoot($projectRoot)
    {
        $this->config->setProjectRoot($projectRoot);
        return $this;
    }
    public function setStripPath($stripPath)
    {
        $this->config->setStripPath($stripPath);
        return $this;
    }
    public function setProjectRootRegex($projectRootRegex)
    {
        $this->config->projectRootRegex = $projectRootRegex;
        return $this;
    }
    public function setFilters(array $filters)
    {
        $this->config->filters = $filters;
        return $this;
    }
    public function setUser(array $user)
    {
        $this->config->user = $user;
        return $this;
    }
    public function setUserId($userId)
    {
        if (!is_array($this->config->user)) {
            $this->config->user = array();
        }
        $this->config->user['id'] = $userId;
        return $this;
    }
    public function setContext($context)
    {
        $this->config->context = $context;
        return $this;
    }
    public function setType($type)
    {
        $this->config->type = $type;
        return $this;
    }
    public function setMetaData(array $metaData)
    {
        $this->config->metaData = $metaData;
        return $this;
    }
    public function setProxySettings(array $proxySettings)
    {
        $this->config->proxySettings = $proxySettings;
        return $this;
    }
    public function setCurlOptions(array $curlOptions)
    {
        $this->config->curlOptions = $curlOptions;
        return $this;
    }
    public function setBeforeNotifyFunction($beforeNotifyFunction)
    {
        $this->config->beforeNotifyFunction = $beforeNotifyFunction;
        return $this;
    }
    public function setErrorReportingLevel($errorReportingLevel)
    {
        $this->config->errorReportingLevel = $errorReportingLevel;
        return $this;
    }
    public function setAutoNotify($autoNotify)
    {
        $this->config->autoNotify = $autoNotify;
        return $this;
    }
    public function setBatchSending($batchSending)
    {
        $this->config->batchSending = $batchSending;
        return $this;
    }
    public function setNotifier($notifier)
    {
        $this->config->notifier = $notifier;
        return $this;
    }
    public function setSendEnvironment($sendEnvironment)
    {
        $this->config->sendEnvironment = $sendEnvironment;
        return $this;
    }
    public function setSendCode($sendCode)
    {
        $this->config->sendCode = $sendCode;
        return $this;
    }
    public function notifyException(Exception $exception, array $metaData = null, $severity = null)
    {
        $error = Bugsnag_Error::fromPHPException($this->config, $this->diagnostics, $exception);
        $error->setSeverity($severity);
        $this->notify($error, $metaData);
    }
    public function notifyError($name, $message, array $metaData = null, $severity = null)
    {
        $error = Bugsnag_Error::fromNamedError($this->config, $this->diagnostics, $name, $message);
        $error->setSeverity($severity);
        $this->notify($error, $metaData);
    }
    public function exceptionHandler($exception)
    {
        $error = Bugsnag_Error::fromPHPException($this->config, $this->diagnostics, $exception);
        $error->setSeverity("error");
        if (!$error->shouldIgnore() && $this->config->autoNotify) {
            $this->notify($error);
        }
    }
    public function errorHandler($errno, $errstr, $errfile = '', $errline = 0)
    {
        $error = Bugsnag_Error::fromPHPError($this->config, $this->diagnostics, $errno, $errstr, $errfile, $errline);
        if (!$error->shouldIgnore() && $this->config->autoNotify) {
            $this->notify($error);
        }
    }
    public function shutdownHandler()
    {
        $lastError = error_get_last();
        if (!is_null($lastError) && Bugsnag_ErrorTypes::isFatal($lastError['type'])) {
            $error = Bugsnag_Error::fromPHPError($this->config, $this->diagnostics, $lastError['type'], $lastError['message'], $lastError['file'], $lastError['line'], true);
            $error->setSeverity("error");
            if (!$error->shouldIgnore() && $this->config->autoNotify) {
                $this->notify($error);
            }
        }
        if ($this->notification) {
            $this->notification->deliver();
            $this->notification = null;
        }
    }
    public function notify(Bugsnag_Error $error, $metaData = array())
    {
        if ($this->sendErrorsOnShutdown()) {
            if (is_null($this->notification)) {
                $this->notification = new Bugsnag_Notification($this->config);
            }
            $this->notification->addError($error, $metaData);
        } else {
            $notif = new Bugsnag_Notification($this->config);
            $notif->addError($error, $metaData);
            $notif->deliver();
        }
    }
    private function sendErrorsOnShutdown()
    {
        return $this->config->batchSending && Bugsnag_Request::isRequest();
    }
}
