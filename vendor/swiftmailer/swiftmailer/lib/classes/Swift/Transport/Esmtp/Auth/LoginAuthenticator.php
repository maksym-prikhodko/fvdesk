<?php
class Swift_Transport_Esmtp_Auth_LoginAuthenticator implements Swift_Transport_Esmtp_Authenticator
{
    public function getAuthKeyword()
    {
        return 'LOGIN';
    }
    public function authenticate(Swift_Transport_SmtpAgent $agent, $username, $password)
    {
        try {
            $agent->executeCommand("AUTH LOGIN\r\n", array(334));
            $agent->executeCommand(sprintf("%s\r\n", base64_encode($username)), array(334));
            $agent->executeCommand(sprintf("%s\r\n", base64_encode($password)), array(235));
            return true;
        } catch (Swift_TransportException $e) {
            $agent->executeCommand("RSET\r\n", array(250));
            return false;
        }
    }
}
