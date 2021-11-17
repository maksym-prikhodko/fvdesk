<?php
namespace Symfony\Component\Security\Core\Validator\Constraints;
use Symfony\Component\Validator\Constraint;
class UserPassword extends Constraint
{
    public $message = 'This value should be the user\'s current password.';
    public $service = 'security.validator.user_password';
    public function validatedBy()
    {
        return $this->service;
    }
}
