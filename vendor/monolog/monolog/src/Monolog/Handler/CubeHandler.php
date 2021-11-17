<?php
namespace Monolog\Handler;
use Monolog\Logger;
class CubeHandler extends AbstractProcessingHandler
{
    private $udpConnection = null;
    private $httpConnection = null;
    private $scheme = null;
    private $host = null;
    private $port = null;
    private $acceptedSchemes = array('http', 'udp');
    public function __construct($url, $level = Logger::DEBUG, $bubble = true)
    {
        $urlInfos = parse_url($url);
        if (!isset($urlInfos['scheme']) || !isset($urlInfos['host']) || !isset($urlInfos['port'])) {
            throw new \UnexpectedValueException('URL "'.$url.'" is not valid');
        }
        if (!in_array($urlInfos['scheme'], $this->acceptedSchemes)) {
            throw new \UnexpectedValueException(
                'Invalid protocol (' . $urlInfos['scheme']  . ').'
                . ' Valid options are ' . implode(', ', $this->acceptedSchemes));
        }
        $this->scheme = $urlInfos['scheme'];
        $this->host = $urlInfos['host'];
        $this->port = $urlInfos['port'];
        parent::__construct($level, $bubble);
    }
    protected function connectUdp()
    {
        if (!extension_loaded('sockets')) {
            throw new MissingExtensionException('The sockets extension is required to use udp URLs with the CubeHandler');
        }
        $this->udpConnection = socket_create(AF_INET, SOCK_DGRAM, 0);
        if (!$this->udpConnection) {
            throw new \LogicException('Unable to create a socket');
        }
        if (!socket_connect($this->udpConnection, $this->host, $this->port)) {
            throw new \LogicException('Unable to connect to the socket at ' . $this->host . ':' . $this->port);
        }
    }
    protected function connectHttp()
    {
        if (!extension_loaded('curl')) {
            throw new \LogicException('The curl extension is needed to use http URLs with the CubeHandler');
        }
        $this->httpConnection = curl_init('http:
        if (!$this->httpConnection) {
            throw new \LogicException('Unable to connect to ' . $this->host . ':' . $this->port);
        }
        curl_setopt($this->httpConnection, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($this->httpConnection, CURLOPT_RETURNTRANSFER, true);
    }
    protected function write(array $record)
    {
        $date = $record['datetime'];
        $data = array('time' => $date->format('Y-m-d\TH:i:s.uO'));
        unset($record['datetime']);
        if (isset($record['context']['type'])) {
            $data['type'] = $record['context']['type'];
            unset($record['context']['type']);
        } else {
            $data['type'] = $record['channel'];
        }
        $data['data'] = $record['context'];
        $data['data']['level'] = $record['level'];
        $this->{'write'.$this->scheme}(json_encode($data));
    }
    private function writeUdp($data)
    {
        if (!$this->udpConnection) {
            $this->connectUdp();
        }
        socket_send($this->udpConnection, $data, strlen($data), 0);
    }
    private function writeHttp($data)
    {
        if (!$this->httpConnection) {
            $this->connectHttp();
        }
        curl_setopt($this->httpConnection, CURLOPT_POSTFIELDS, '['.$data.']');
        curl_setopt($this->httpConnection, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen('['.$data.']'))
        );
        return curl_exec($this->httpConnection);
    }
}
