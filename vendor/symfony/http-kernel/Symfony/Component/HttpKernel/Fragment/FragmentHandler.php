<?php
namespace Symfony\Component\HttpKernel\Fragment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
class FragmentHandler
{
    private $debug;
    private $renderers = array();
    private $request;
    private $requestStack;
    public function __construct(array $renderers = array(), $debug = false, RequestStack $requestStack = null)
    {
        $this->requestStack = $requestStack;
        foreach ($renderers as $renderer) {
            $this->addRenderer($renderer);
        }
        $this->debug = $debug;
    }
    public function addRenderer(FragmentRendererInterface $renderer)
    {
        $this->renderers[$renderer->getName()] = $renderer;
    }
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }
    public function render($uri, $renderer = 'inline', array $options = array())
    {
        if (!isset($options['ignore_errors'])) {
            $options['ignore_errors'] = !$this->debug;
        }
        if (!isset($this->renderers[$renderer])) {
            throw new \InvalidArgumentException(sprintf('The "%s" renderer does not exist.', $renderer));
        }
        if (!$request = $this->getRequest()) {
            throw new \LogicException('Rendering a fragment can only be done when handling a Request.');
        }
        return $this->deliver($this->renderers[$renderer]->render($uri, $request, $options));
    }
    protected function deliver(Response $response)
    {
        if (!$response->isSuccessful()) {
            throw new \RuntimeException(sprintf('Error when rendering "%s" (Status code is %s).', $this->getRequest()->getUri(), $response->getStatusCode()));
        }
        if (!$response instanceof StreamedResponse) {
            return $response->getContent();
        }
        $response->sendContent();
    }
    private function getRequest()
    {
        return $this->requestStack ? $this->requestStack->getCurrentRequest() : $this->request;
    }
}
