<?php
namespace Symfony\Component\Security\Core\Tests\Validator\Constraints;
use Symfony\Component\Validator\Validation;
class LegacyUserPasswordValidatorApiTest extends UserPasswordValidatorTest
{
    protected function getApiVersion()
    {
        return Validation::API_VERSION_2_5_BC;
    }
}
