<?php
namespace Symfony\Component\HttpKernel\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\Util\ValueExporter;
abstract class DataCollector implements DataCollectorInterface, \Serializable
{
    protected $data = array();
    private $valueExporter;
    public function serialize()
    {
        return serialize($this->data);
    }
    public function unserialize($data)
    {
        $this->data = unserialize($data);
    }
    protected function varToString($var)
    {
        if (null === $this->valueExporter) {
            $this->valueExporter = new ValueExporter();
        }
        return $this->valueExporter->exportValue($var);
    }
}
