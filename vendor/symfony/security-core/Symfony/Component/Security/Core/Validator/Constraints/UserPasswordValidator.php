<?php
namespace Symfony\Component\Security\Core\Validator\Constraints;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
class UserPasswordValidator extends ConstraintValidator
{
    private $securityContext;
    private $encoderFactory;
    public function __construct(SecurityContextInterface $securityContext, EncoderFactoryInterface $encoderFactory)
    {
        $this->securityContext = $securityContext;
        $this->encoderFactory = $encoderFactory;
    }
    public function validate($password, Constraint $constraint)
    {
        if (!$constraint instanceof UserPassword) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\UserPassword');
        }
        $user = $this->securityContext->getToken()->getUser();
        if (!$user instanceof UserInterface) {
            throw new ConstraintDefinitionException('The User object must implement the UserInterface interface.');
        }
        $encoder = $this->encoderFactory->getEncoder($user);
        if (!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            $this->context->addViolation($constraint->message);
        }
    }
}
