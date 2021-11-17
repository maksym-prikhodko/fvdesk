<?php
namespace PhpSpec\Formatter\Html;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Formatter\Template as TemplateInterface;
class ReportPendingItem
{
    private $template;
    private $event;
    private static $pendingExamplesCount = 1;
    public function __construct(TemplateInterface $template, ExampleEvent $event)
    {
        $this->template = $template;
        $this->event = $event;
    }
    public function write()
    {
        $this->template->render(Template::DIR.'/Template/ReportPending.html', array(
            'title' => $this->event->getTitle(),
            'pendingExamplesCount' => self::$pendingExamplesCount
        ));
        self::$pendingExamplesCount++;
    }
}
