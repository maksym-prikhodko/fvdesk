<?php
namespace Symfony\Component\VarDumper\Caster;
use Doctrine\Common\Proxy\Proxy as CommonProxy;
use Doctrine\ORM\Proxy\Proxy as OrmProxy;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\VarDumper\Cloner\Stub;
class DoctrineCaster
{
    public static function castCommonProxy(CommonProxy $proxy, array $a, Stub $stub, $isNested)
    {
        unset(
            $a['__cloner__'],
            $a['__initializer__']
        );
        $stub->cut += 2;
        return $a;
    }
    public static function castOrmProxy(OrmProxy $proxy, array $a, Stub $stub, $isNested)
    {
        $prefix = "\0Doctrine\\ORM\\Proxy\\Proxy\0";
        unset(
            $a[$prefix.'_entityPersister'],
            $a[$prefix.'_identifier']
        );
        $stub->cut += 2;
        return $a;
    }
    public static function castPersistentCollection(PersistentCollection $coll, array $a, Stub $stub, $isNested)
    {
        $prefix = "\0Doctrine\\ORM\\PersistentCollection\0";
        $a[$prefix.'snapshot'] = new CutStub($a[$prefix.'snapshot']);
        $a[$prefix.'association'] = new CutStub($a[$prefix.'association']);
        $a[$prefix.'typeClass'] = new CutStub($a[$prefix.'typeClass']);
        return $a;
    }
}
