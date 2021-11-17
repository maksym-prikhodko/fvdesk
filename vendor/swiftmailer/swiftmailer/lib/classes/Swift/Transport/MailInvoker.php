<?php
interface Swift_Transport_MailInvoker
{
    public function mail($to, $subject, $body, $headers = null, $extraParams = null);
}
