<?php
class Swift_Transport_MailTransport implements Swift_Transport
{
    private $_extraParams = '-f%s';
    private $_eventDispatcher;
    private $_invoker;
    public function __construct(Swift_Transport_MailInvoker $invoker, Swift_Events_EventDispatcher $eventDispatcher)
    {
        $this->_invoker = $invoker;
        $this->_eventDispatcher = $eventDispatcher;
    }
    public function isStarted()
    {
        return false;
    }
    public function start()
    {
    }
    public function stop()
    {
    }
    public function setExtraParams($params)
    {
        $this->_extraParams = $params;
        return $this;
    }
    public function getExtraParams()
    {
        return $this->_extraParams;
    }
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $failedRecipients = (array) $failedRecipients;
        if ($evt = $this->_eventDispatcher->createSendEvent($this, $message)) {
            $this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }
        $count = (
            count((array) $message->getTo())
            + count((array) $message->getCc())
            + count((array) $message->getBcc())
            );
        $toHeader = $message->getHeaders()->get('To');
        $subjectHeader = $message->getHeaders()->get('Subject');
        if (!$toHeader) {
            $this->_throwException(new Swift_TransportException('Cannot send message without a recipient'));
        }
        $to = $toHeader->getFieldBody();
        $subject = $subjectHeader ? $subjectHeader->getFieldBody() : '';
        $reversePath = $this->_getReversePath($message);
        $message->getHeaders()->remove('To');
        $message->getHeaders()->remove('Subject');
        $messageStr = $message->toString();
        $message->getHeaders()->set($toHeader);
        $message->getHeaders()->set($subjectHeader);
        if (false !== $endHeaders = strpos($messageStr, "\r\n\r\n")) {
            $headers = substr($messageStr, 0, $endHeaders)."\r\n"; 
            $body = substr($messageStr, $endHeaders + 4);
        } else {
            $headers = $messageStr."\r\n";
            $body = '';
        }
        unset($messageStr);
        if ("\r\n" != PHP_EOL) {
            $headers = str_replace("\r\n", PHP_EOL, $headers);
            $body = str_replace("\r\n", PHP_EOL, $body);
        } else {
            $headers = str_replace("\r\n.", "\r\n..", $headers);
            $body = str_replace("\r\n.", "\r\n..", $body);
        }
        if ($this->_invoker->mail($to, $subject, $body, $headers,
            sprintf($this->_extraParams, $reversePath))) {
            if ($evt) {
                $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
                $evt->setFailedRecipients($failedRecipients);
                $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
            }
        } else {
            $failedRecipients = array_merge(
                $failedRecipients,
                array_keys((array) $message->getTo()),
                array_keys((array) $message->getCc()),
                array_keys((array) $message->getBcc())
                );
            if ($evt) {
                $evt->setResult(Swift_Events_SendEvent::RESULT_FAILED);
                $evt->setFailedRecipients($failedRecipients);
                $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
            }
            $message->generateId();
            $count = 0;
        }
        return $count;
    }
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->_eventDispatcher->bindEventListener($plugin);
    }
    protected function _throwException(Swift_TransportException $e)
    {
        if ($evt = $this->_eventDispatcher->createTransportExceptionEvent($this, $e)) {
            $this->_eventDispatcher->dispatchEvent($evt, 'exceptionThrown');
            if (!$evt->bubbleCancelled()) {
                throw $e;
            }
        } else {
            throw $e;
        }
    }
    private function _getReversePath(Swift_Mime_Message $message)
    {
        $return = $message->getReturnPath();
        $sender = $message->getSender();
        $from = $message->getFrom();
        $path = null;
        if (!empty($return)) {
            $path = $return;
        } elseif (!empty($sender)) {
            $keys = array_keys($sender);
            $path = array_shift($keys);
        } elseif (!empty($from)) {
            $keys = array_keys($from);
            $path = array_shift($keys);
        }
        return $path;
    }
}
