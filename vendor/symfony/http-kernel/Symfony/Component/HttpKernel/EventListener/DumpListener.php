<?php
namespace Symfony\Component\HttpKernel\EventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use Symfony\Component\VarDumper\VarDumper;
class DumpListener implements EventSubscriberInterface
{
    private $cloner;
    private $dumper;
    public function __construct(ClonerInterface $cloner, DataDumperInterface $dumper)
    {
        $this->cloner = $cloner;
        $this->dumper = $dumper;
    }
    public function configure()
    {
        $cloner = $this->cloner;
        $dumper = $this->dumper;
        VarDumper::setHandler(function ($var) use ($cloner, $dumper) {
            $dumper->dump($cloner->cloneVar($var));
        });
    }
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => array('configure', 1024));
    }
}