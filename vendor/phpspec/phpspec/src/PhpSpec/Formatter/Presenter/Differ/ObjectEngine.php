<?php
namespace PhpSpec\Formatter\Presenter\Differ;
use SebastianBergmann\Exporter\Exporter;
class ObjectEngine implements EngineInterface
{
    private $exporter;
    private $stringDiffer;
    public function __construct(Exporter $exporter, StringEngine $stringDiffer)
    {
        $this->exporter = $exporter;
        $this->stringDiffer = $stringDiffer;
    }
    public function supports($expected, $actual)
    {
        return is_object($expected) && is_object($actual);
    }
    public function compare($expected, $actual)
    {
        return $this->stringDiffer->compare(
            $this->exporter->export($expected),
            $this->exporter->export($actual)
        );
    }
}
