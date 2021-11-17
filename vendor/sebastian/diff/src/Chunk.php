<?php
namespace SebastianBergmann\Diff;
class Chunk
{
    private $start;
    private $startRange;
    private $end;
    private $endRange;
    private $lines;
    public function __construct($start = 0, $startRange = 1, $end = 0, $endRange = 1, array $lines = array())
    {
        $this->start      = (int) $start;
        $this->startRange = (int) $startRange;
        $this->end        = (int) $end;
        $this->endRange   = (int) $endRange;
        $this->lines      = $lines;
    }
    public function getStart()
    {
        return $this->start;
    }
    public function getStartRange()
    {
        return $this->startRange;
    }
    public function getEnd()
    {
        return $this->end;
    }
    public function getEndRange()
    {
        return $this->endRange;
    }
    public function getLines()
    {
        return $this->lines;
    }
    public function setLines(array $lines)
    {
        $this->lines = $lines;
    }
}
