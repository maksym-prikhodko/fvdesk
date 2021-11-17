<?php
class Swift_Transport_Esmtp_Auth_PlainAuthenticator implements Swift_Transport_Esmtp_Authenticator
{
    public function getAuthKeyword()
    {
        return 'PLAIN';
    }
    public function authenticate(Swift_Transport_SmtpAgent $agent, $username, $password)
    {
        try {
            $message = base64_encode($username.chr(0).$username.chr(0).$password);
            $agent->executeCommand(sprintf("AUTH PLAIN %s\r\n", $message), array(235));
            return true;
        } catch (Swift_TransportException $e) {
            $agent->executeCommand("RSET\r\n", array(250));
            return false;
        }
    }
}
