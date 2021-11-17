<?php
class EsmtpTransportFixture extends Swift_Transport_EsmtpTransport
{
    private function _sortHandlers($a, $b)
    {
        return 1;
    }
}
