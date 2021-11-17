<?php
class Swift_Mailer_ArrayRecipientIterator implements Swift_Mailer_RecipientIterator
{
    private $_recipients = array();
    public function __construct(array $recipients)
    {
        $this->_recipients = $recipients;
    }
    public function hasNext()
    {
        return !empty($this->_recipients);
    }
    public function nextRecipient()
    {
        return array_splice($this->_recipients, 0, 1);
    }
}
