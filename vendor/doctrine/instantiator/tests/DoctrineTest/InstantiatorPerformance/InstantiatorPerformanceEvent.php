<?php
namespace DoctrineTest\InstantiatorPerformance;
use Athletic\AthleticEvent;
use Doctrine\Instantiator\Instantiator;
class InstantiatorPerformanceEvent extends AthleticEvent
{
    private $instantiator;
    protected function setUp()
    {
        $this->instantiator = new Instantiator();
        $this->instantiator->instantiate(__CLASS__);
        $this->instantiator->instantiate('ArrayObject');
        $this->instantiator->instantiate('DoctrineTest\\InstantiatorTestAsset\\SimpleSerializableAsset');
        $this->instantiator->instantiate('DoctrineTest\\InstantiatorTestAsset\\SerializableArrayObjectAsset');
        $this->instantiator->instantiate('DoctrineTest\\InstantiatorTestAsset\\UnCloneableAsset');
    }
    public function testInstantiateSelf()
    {
        $this->instantiator->instantiate(__CLASS__);
    }
    public function testInstantiateInternalClass()
    {
        $this->instantiator->instantiate('ArrayObject');
    }
    public function testInstantiateSimpleSerializableAssetClass()
    {
        $this->instantiator->instantiate('DoctrineTest\\InstantiatorTestAsset\\SimpleSerializableAsset');
    }
    public function testInstantiateSerializableArrayObjectAsset()
    {
        $this->instantiator->instantiate('DoctrineTest\\InstantiatorTestAsset\\SerializableArrayObjectAsset');
    }
    public function testInstantiateUnCloneableAsset()
    {
        $this->instantiator->instantiate('DoctrineTest\\InstantiatorTestAsset\\UnCloneableAsset');
    }
}
