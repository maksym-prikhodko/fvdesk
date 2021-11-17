<?php
namespace Symfony\Component\Security\Core\Authentication\Token;
use Symfony\Component\Security\Core\Role\RoleInterface;
interface TokenInterface extends \Serializable
{
    public function __toString();
    public function getRoles();
    public function getCredentials();
    public function getUser();
    public function setUser($user);
    public function getUsername();
    public function isAuthenticated();
    public function setAuthenticated($isAuthenticated);
    public function eraseCredentials();
    public function getAttributes();
    public function setAttributes(array $attributes);
    public function hasAttribute($name);
    public function getAttribute($name);
    public function setAttribute($name, $value);
}
