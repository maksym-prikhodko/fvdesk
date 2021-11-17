<?php
namespace Symfony\Component\Security\Core\Util;
use Doctrine\Common\Util\ClassUtils as DoctrineClassUtils;
class ClassUtils
{
    const MARKER = '__CG__';
    const MARKER_LENGTH = 6;
    private function __construct()
    {
    }
    public static function getRealClass($object)
    {
        $class = is_object($object) ? get_class($object) : $object;
        if (false === $pos = strrpos($class, '\\'.self::MARKER.'\\')) {
            return $class;
        }
        return substr($class, $pos + self::MARKER_LENGTH + 2);
    }
}
