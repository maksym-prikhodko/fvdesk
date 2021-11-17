<?php
namespace Monolog\Handler;
use Monolog\Logger;
class FlowdockHandler extends SocketHandler
{
    protected $apiToken;
    public function __construct($apiToken, $level = Logger::DEBUG, $bubble = true)
    {
        if (!extension_loaded('openssl')) {
            throw new MissingExtensionException('The OpenSSL PHP extension is required to use the FlowdockHandler');
        }
        parent::__construct('ssl:
        $this->apiToken = $apiToken;
    }
    protected function write(array $record)
    {
        parent::write($record);
        $this->closeSocket();
    }
    protected function generateDataStream($record)
    {
        $content = $this->buildContent($record);
        return $this->buildHeader($content) . $content;
    }
    private function buildContent($record)
    {
        return json_encode($record['formatted']['flowdock']);
    }
    private function buildHeader($content)
    {
        $header = "POST /v1/messages/team_inbox/" . $this->apiToken . " HTTP/1.1\r\n";
        $header .= "Host: api.flowdock.com\r\n";
        $header .= "Content-Type: application/json\r\n";
        $header .= "Content-Length: " . strlen($content) . "\r\n";
        $header .= "\r\n";
        return $header;
    }
}
