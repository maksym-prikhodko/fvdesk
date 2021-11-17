<?php
namespace Symfony\Component\Security\Core\Authentication\Token;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
abstract class AbstractToken implements TokenInterface
{
    private $user;
    private $roles = array();
    private $authenticated = false;
    private $attributes = array();
    public function __construct(array $roles = array())
    {
        foreach ($roles as $role) {
            if (is_string($role)) {
                $role = new Role($role);
            } elseif (!$role instanceof RoleInterface) {
                throw new \InvalidArgumentException(sprintf('$roles must be an array of strings, or RoleInterface instances, but got %s.', gettype($role)));
            }
            $this->roles[] = $role;
        }
    }
    public function getRoles()
    {
        return $this->roles;
    }
    public function getUsername()
    {
        if ($this->user instanceof UserInterface) {
            return $this->user->getUsername();
        }
        return (string) $this->user;
    }
    public function getUser()
    {
        return $this->user;
    }
    public function setUser($user)
    {
        if (!($user instanceof UserInterface || (is_object($user) && method_exists($user, '__toString')) || is_string($user))) {
            throw new \InvalidArgumentException('$user must be an instanceof UserInterface, an object implementing a __toString method, or a primitive string.');
        }
        if (null === $this->user) {
            $changed = false;
        } elseif ($this->user instanceof UserInterface) {
            if (!$user instanceof UserInterface) {
                $changed = true;
            } else {
                $changed = $this->hasUserChanged($user);
            }
        } elseif ($user instanceof UserInterface) {
            $changed = true;
        } else {
            $changed = (string) $this->user !== (string) $user;
        }
        if ($changed) {
            $this->setAuthenticated(false);
        }
        $this->user = $user;
    }
    public function isAuthenticated()
    {
        return $this->authenticated;
    }
    public function setAuthenticated($authenticated)
    {
        $this->authenticated = (bool) $authenticated;
    }
    public function eraseCredentials()
    {
        if ($this->getUser() instanceof UserInterface) {
            $this->getUser()->eraseCredentials();
        }
    }
    public function serialize()
    {
        return serialize(
            array(
                is_object($this->user) ? clone $this->user : $this->user,
                $this->authenticated,
                $this->roles,
                $this->attributes,
            )
        );
    }
    public function unserialize($serialized)
    {
        list($this->user, $this->authenticated, $this->roles, $this->attributes) = unserialize($serialized);
    }
    public function getAttributes()
    {
        return $this->attributes;
    }
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }
    public function getAttribute($name)
    {
        if (!array_key_exists($name, $this->attributes)) {
            throw new \InvalidArgumentException(sprintf('This token has no "%s" attribute.', $name));
        }
        return $this->attributes[$name];
    }
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }
    public function __toString()
    {
        $class = get_class($this);
        $class = substr($class, strrpos($class, '\\') + 1);
        $roles = array();
        foreach ($this->roles as $role) {
            $roles[] = $role->getRole();
        }
        return sprintf('%s(user="%s", authenticated=%s, roles="%s")', $class, $this->getUsername(), json_encode($this->authenticated), implode(', ', $roles));
    }
    private function hasUserChanged(UserInterface $user)
    {
        if (!($this->user instanceof UserInterface)) {
            throw new \BadMethodCallException('Method "hasUserChanged" should be called when current user class is instance of "UserInterface".');
        }
        if ($this->user instanceof EquatableInterface) {
            return !(bool) $this->user->isEqualTo($user);
        }
        if ($this->user->getPassword() !== $user->getPassword()) {
            return true;
        }
        if ($this->user->getSalt() !== $user->getSalt()) {
            return true;
        }
        if ($this->user->getUsername() !== $user->getUsername()) {
            return true;
        }
        if ($this->user instanceof AdvancedUserInterface && $user instanceof AdvancedUserInterface) {
            if ($this->user->isAccountNonExpired() !== $user->isAccountNonExpired()) {
                return true;
            }
            if ($this->user->isAccountNonLocked() !== $user->isAccountNonLocked()) {
                return true;
            }
            if ($this->user->isCredentialsNonExpired() !== $user->isCredentialsNonExpired()) {
                return true;
            }
            if ($this->user->isEnabled() !== $user->isEnabled()) {
                return true;
            }
        } elseif ($this->user instanceof AdvancedUserInterface xor $user instanceof AdvancedUserInterface) {
            return true;
        }
        return false;
    }
}
