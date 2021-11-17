<?php
namespace Symfony\Component\Security\Core\Authentication\RememberMe;
interface PersistentTokenInterface
{
    public function getClass();
    public function getUsername();
    public function getSeries();
    public function getTokenValue();
    public function getLastUsed();
}
