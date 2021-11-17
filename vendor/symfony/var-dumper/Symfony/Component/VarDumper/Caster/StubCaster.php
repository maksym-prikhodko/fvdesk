<?php
namespace Symfony\Component\VarDumper\Caster;
use Symfony\Component\VarDumper\Cloner\Stub;
class StubCaster
{
    public static function castStub(Stub $c, array $a, Stub $stub, $isNested)
    {
        if ($isNested) {
            $stub->type = $c->type;
            $stub->class = $c->class;
            $stub->value = $c->value;
            $stub->handle = $c->handle;
            $stub->cut = $c->cut;
            return array();
        }
    }
    public static function cutInternals($obj, array $a, Stub $stub, $isNested)
    {
        if ($isNested) {
            $stub->cut += count($a);
            return array();
        }
        return $a;
    }
}
