<?php
namespace Symfony\Component\Routing\Generator;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContextAwareInterface;
interface UrlGeneratorInterface extends RequestContextAwareInterface
{
    const ABSOLUTE_URL = true;
    const ABSOLUTE_PATH = false;
    const RELATIVE_PATH = 'relative';
    const NETWORK_PATH = 'network';
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH);
}
