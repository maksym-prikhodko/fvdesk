<?php
namespace SebastianBergmann\Diff;
class Line
{
    const ADDED = 1;
    const REMOVED = 2;
    const UNCHANGED = 3;
    private $type;
    private $content;
    public function __construct($type = self::UNCHANGED, $content = '')
    {
        $this->type    = $type;
        $this->content = $content;
    }
    public function getContent()
    {
        return $this->content;
    }
    public function getType()
    {
        return $this->type;
    }
}
