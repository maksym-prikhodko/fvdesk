<?php
namespace Symfony\Component\Routing\Matcher\Dumper;
use Symfony\Component\Routing\Route;
class DumperRoute
{
    private $name;
    private $route;
    public function __construct($name, Route $route)
    {
        $this->name = $name;
        $this->route = $route;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getRoute()
    {
        return $this->route;
    }
}
