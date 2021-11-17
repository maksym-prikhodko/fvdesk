<?php
namespace SebastianBergmann\Diff;
class Diff
{
    private $from;
    private $to;
    private $chunks;
    public function __construct($from, $to, array $chunks = array())
    {
        $this->from   = $from;
        $this->to     = $to;
        $this->chunks = $chunks;
    }
    public function getFrom()
    {
        return $this->from;
    }
    public function getTo()
    {
        return $this->to;
    }
    public function getChunks()
    {
        return $this->chunks;
    }
    public function setChunks(array $chunks)
    {
        $this->chunks = $chunks;
    }
}
