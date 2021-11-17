<?php
namespace ClassPreloader;
class ClassList
{
    protected $head;
    protected $current;
    public function __construct()
    {
        $this->clear();
    }
    public function clear()
    {
        $this->head = new ClassNode();
        $this->current = $this->head;
    }
    public function next()
    {
        if (isset($this->current->next)) {
            $this->current = $this->current->next;
        } else {
            $this->current->next = new ClassNode(null, $this->current);
            $this->current = $this->current->next;
        }
    }
    public function push($value)
    {
        if (!$this->current->value) {
            $this->current->value = $value;
        } else {
            $temp = $this->current;
            $this->current = new ClassNode($value, $temp->prev);
            $this->current->next = $temp;
            $temp->prev = $this->current;
            if ($temp === $this->head) {
                $this->head = $this->current;
            } else {
                $this->current->prev->next = $this->current;
            }
        }
    }
    public function getClasses()
    {
        $classes = array();
        $current = $this->head;
        while ($current && $current->value) {
            $classes[] = $current->value;
            $current = $current->next;
        }
        return array_filter($classes);
    }
}
