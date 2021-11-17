<?php
namespace Symfony\Component\Security\Core;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
interface SecurityContextInterface extends TokenStorageInterface, AuthorizationCheckerInterface
{
    const ACCESS_DENIED_ERROR = Security::ACCESS_DENIED_ERROR;
    const AUTHENTICATION_ERROR = Security::AUTHENTICATION_ERROR;
    const LAST_USERNAME = Security::LAST_USERNAME;
}
