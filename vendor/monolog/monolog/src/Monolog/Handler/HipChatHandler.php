<?php
namespace Monolog\Handler;
use Monolog\Logger;
class HipChatHandler extends SocketHandler
{
    const MAXIMUM_NAME_LENGTH = 15;
    const MAXIMUM_MESSAGE_LENGTH = 9500;
    private $token;
    private $room;
    private $name;
    private $notify;
    private $format;
    private $host;
    public function __construct($token, $room, $name = 'Monolog', $notify = false, $level = Logger::CRITICAL, $bubble = true, $useSSL = true, $format = 'text', $host = 'api.hipchat.com')
    {
        if (!$this->validateStringLength($name, static::MAXIMUM_NAME_LENGTH)) {
            throw new \InvalidArgumentException('The supplied name is too long. HipChat\'s v1 API supports names up to 15 UTF-8 characters.');
        }
        $connectionString = $useSSL ? 'ssl:
        parent::__construct($connectionString, $level, $bubble);
        $this->token = $token;
        $this->name = $name;
        $this->notify = $notify;
        $this->room = $room;
        $this->format = $format;
        $this->host = $host;
    }
    protected function generateDataStream($record)
    {
        $content = $this->buildContent($record);
        return $this->buildHeader($content) . $content;
    }
    private function buildContent($record)
    {
        $dataArray = array(
            'from' => $this->name,
            'room_id' => $this->room,
            'notify' => $this->notify,
            'message' => $record['formatted'],
            'message_format' => $this->format,
            'color' => $this->getAlertColor($record['level']),
        );
        return http_build_query($dataArray);
    }
    private function buildHeader($content)
    {
        $header = "POST /v1/rooms/message?format=json&auth_token=".$this->token." HTTP/1.1\r\n";
        $header .= "Host: {$this->host}\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($content) . "\r\n";
        $header .= "\r\n";
        return $header;
    }
    protected function getAlertColor($level)
    {
        switch (true) {
            case $level >= Logger::ERROR:
                return 'red';
            case $level >= Logger::WARNING:
                return 'yellow';
            case $level >= Logger::INFO:
                return 'green';
            case $level == Logger::DEBUG:
                return 'gray';
            default:
                return 'yellow';
        }
    }
    protected function write(array $record)
    {
        parent::write($record);
        $this->closeSocket();
    }
    public function handleBatch(array $records)
    {
        if (count($records) == 0) {
            return true;
        }
        $batchRecords = $this->combineRecords($records);
        $handled = false;
        foreach ($batchRecords as $batchRecord) {
            if ($this->isHandling($batchRecord)) {
                $this->write($batchRecord);
                $handled = true;
            }
        }
        if (!$handled) {
            return false;
        }
        return false === $this->bubble;
    }
    private function combineRecords($records)
    {
        $batchRecord = null;
        $batchRecords = array();
        $messages = array();
        $formattedMessages = array();
        $level = 0;
        $levelName = null;
        $datetime = null;
        foreach ($records as $record) {
            $record = $this->processRecord($record);
            if ($record['level'] > $level) {
                $level = $record['level'];
                $levelName = $record['level_name'];
            }
            if (null === $datetime) {
                $datetime = $record['datetime'];
            }
            $messages[] = $record['message'];
            $messageStr = implode(PHP_EOL, $messages);
            $formattedMessages[] = $this->getFormatter()->format($record);
            $formattedMessageStr = implode('', $formattedMessages);
            $batchRecord = array(
                'message'   => $messageStr,
                'formatted' => $formattedMessageStr,
                'context'   => array(),
                'extra'     => array(),
            );
            if (!$this->validateStringLength($batchRecord['formatted'], static::MAXIMUM_MESSAGE_LENGTH)) {
                $lastMessage = array_pop($messages);
                $lastFormattedMessage = array_pop($formattedMessages);
                $batchRecord['message'] = implode(PHP_EOL, $messages);
                $batchRecord['formatted'] = implode('', $formattedMessages);
                $batchRecords[] = $batchRecord;
                $messages = array($lastMessage);
                $formattedMessages = array($lastFormattedMessage);
                $batchRecord = null;
            }
        }
        if (null !== $batchRecord) {
            $batchRecords[] = $batchRecord;
        }
        foreach ($batchRecords as &$batchRecord) {
            $batchRecord = array_merge(
                $batchRecord,
                array(
                    'level'      => $level,
                    'level_name' => $levelName,
                    'datetime'   => $datetime
                )
            );
        }
        return $batchRecords;
    }
    private function validateStringLength($str, $length)
    {
        if (function_exists('mb_strlen')) {
            return (mb_strlen($str) <= $length);
        }
        return (strlen($str) <= $length);
    }
}