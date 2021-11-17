<?php
namespace SebastianBergmann\Comparator;
class Author
{
    public $books = array();
    private $name = '';
    public function __construct($name)
    {
        $this->name = $name;
    }
}
