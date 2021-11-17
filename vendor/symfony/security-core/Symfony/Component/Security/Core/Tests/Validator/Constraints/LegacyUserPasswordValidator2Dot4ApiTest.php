<?php
namespace Symfony\Component\Security\Core\Tests\Validator\Constraints;
use Symfony\Component\Validator\Validation;
class LegacyUserPasswordValidator2Dot4ApiTest extends UserPasswordValidatorTest
{
    protected function getApiVersion()
    {
        return Validation::API_VERSION_2_4;
    }
}
