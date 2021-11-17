<?php
namespace DoctrineTest\InstantiatorTestAsset;
use ArrayObject;
class WakeUpNoticesAsset extends ArrayObject
{
    public function __wakeup()
    {
        trigger_error('Something went bananas while un-serializing this instance');
    }
}
