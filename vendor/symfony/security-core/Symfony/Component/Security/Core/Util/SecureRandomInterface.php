<?php
namespace Symfony\Component\Security\Core\Util;
interface SecureRandomInterface
{
    public function nextBytes($nbBytes);
}
