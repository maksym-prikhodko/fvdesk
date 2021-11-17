<?php
namespace PhpParser;
class Error extends \RuntimeException
{
    protected $rawMessage;
    protected $rawLine;
    public function __construct($message, $line = -1) {
        $this->rawMessage = (string) $message;
        $this->rawLine    = (int) $line;
        $this->updateMessage();
    }
    public function getRawMessage() {
        return $this->rawMessage;
    }
    public function setRawMessage($message) {
        $this->rawMessage = (string) $message;
        $this->updateMessage();
    }
    public function getRawLine() {
        return $this->rawLine;
    }
    public function setRawLine($line) {
        $this->rawLine = (int) $line;
        $this->updateMessage();
    }
    protected function updateMessage() {
        $this->message = $this->rawMessage;
        if (-1 === $this->rawLine) {
            $this->message .= ' on unknown line';
        } else {
            $this->message .= ' on line ' . $this->rawLine;
        }
    }
}
