<?php
class Swift_Transport_SimpleMailInvoker implements Swift_Transport_MailInvoker
{
    public function mail($to, $subject, $body, $headers = null, $extraParams = null)
    {
        if (!ini_get('safe_mode')) {
            return @mail($to, $subject, $body, $headers, $extraParams);
        } else {
            return @mail($to, $subject, $body, $headers);
        }
    }
}
