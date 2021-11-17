<?php
namespace Symfony\Component\VarDumper\Caster;
use Symfony\Component\VarDumper\Cloner\Stub;
class ReflectionCaster
{
    public static function castReflector(\Reflector $c, array $a, Stub $stub, $isNested)
    {
        $a["\0~\0reflection"] = $c->__toString();
        return $a;
    }
    public static function castClosure(\Closure $c, array $a, Stub $stub, $isNested)
    {
        $stub->class = 'Closure'; 
        $a = static::castReflector(new \ReflectionFunction($c), $a, $stub, $isNested);
        unset($a["\0+\0000"], $a['name'], $a["\0+\0this"], $a["\0+\0parameter"]);
        return $a;
    }
}
