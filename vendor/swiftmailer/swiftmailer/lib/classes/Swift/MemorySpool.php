<?php
class Swift_MemorySpool implements Swift_Spool
{
    protected $messages = array();
    public function isStarted()
    {
        return true;
    }
    public function start()
    {
    }
    public function stop()
    {
    }
    public function queueMessage(Swift_Mime_Message $message)
    {
        $this->messages[] = clone $message;
        return true;
    }
    public function flushQueue(Swift_Transport $transport, &$failedRecipients = null)
    {
        if (!$this->messages) {
            return 0;
        }
        if (!$transport->isStarted()) {
            $transport->start();
        }
        $count = 0;
        while ($message = array_pop($this->messages)) {
            $count += $transport->send($message, $failedRecipients);
        }
        return $count;
    }
}
