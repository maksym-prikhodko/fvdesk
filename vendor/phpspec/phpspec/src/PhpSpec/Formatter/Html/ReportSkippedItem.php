<?php
namespace PhpSpec\Formatter\Html;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Formatter\Template as TemplateInterface;
class ReportSkippedItem
{
    private $template;
    private $event;
    public function __construct(TemplateInterface $template, ExampleEvent $event)
    {
        $this->template = $template;
        $this->event    = $event;
    }
    public function write()
    {
        $this->template->render(Template::DIR.'/Template/ReportSkipped.html', array(
            'title' => htmlentities(strip_tags($this->event->getTitle())),
            'message' => htmlentities(strip_tags($this->event->getMessage())),
        ));
    }
}
