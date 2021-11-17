<?php
namespace Symfony\Component\HttpKernel\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
class Ssi implements SurrogateInterface
{
    private $contentTypes;
    private $phpEscapeMap = array(
        array('<?', '<%', '<s', '<S'),
        array('<?php echo "<?"; ?>', '<?php echo "<%"; ?>', '<?php echo "<s"; ?>', '<?php echo "<S"; ?>'),
    );
    public function __construct(array $contentTypes = array('text/html', 'text/xml', 'application/xhtml+xml', 'application/xml'))
    {
        $this->contentTypes = $contentTypes;
    }
    public function getName()
    {
        return 'ssi';
    }
    public function createCacheStrategy()
    {
        return new ResponseCacheStrategy();
    }
    public function hasSurrogateCapability(Request $request)
    {
        if (null === $value = $request->headers->get('Surrogate-Capability')) {
            return false;
        }
        return false !== strpos($value, 'SSI/1.0');
    }
    public function addSurrogateCapability(Request $request)
    {
        $current = $request->headers->get('Surrogate-Capability');
        $new = 'symfony2="SSI/1.0"';
        $request->headers->set('Surrogate-Capability', $current ? $current.', '.$new : $new);
    }
    public function addSurrogateControl(Response $response)
    {
        if (false !== strpos($response->getContent(), '<!--#include')) {
            $response->headers->set('Surrogate-Control', 'content="SSI/1.0"');
        }
    }
    public function needsParsing(Response $response)
    {
        if (!$control = $response->headers->get('Surrogate-Control')) {
            return false;
        }
        return (bool) preg_match('#content="[^"]*SSI/1.0[^"]*"#', $control);
    }
    public function renderIncludeTag($uri, $alt = null, $ignoreErrors = true, $comment = '')
    {
        return sprintf('<!--#include virtual="%s" -->', $uri);
    }
    public function process(Request $request, Response $response)
    {
        $this->request = $request;
        $type = $response->headers->get('Content-Type');
        if (empty($type)) {
            $type = 'text/html';
        }
        $parts = explode(';', $type);
        if (!in_array($parts[0], $this->contentTypes)) {
            return $response;
        }
        $content = $response->getContent();
        $chunks = preg_split('#<!--\#include\s+(.*?)\s*-->#', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $chunks[0] = str_replace($this->phpEscapeMap[0], $this->phpEscapeMap[1], $chunks[0]);
        $i = 1;
        while (isset($chunks[$i])) {
            $options = array();
            preg_match_all('/(virtual)="([^"]*?)"/', $chunks[$i], $matches, PREG_SET_ORDER);
            foreach ($matches as $set) {
                $options[$set[1]] = $set[2];
            }
            if (!isset($options['virtual'])) {
                throw new \RuntimeException('Unable to process an SSI tag without a "virtual" attribute.');
            }
            $chunks[$i] = sprintf('<?php echo $this->surrogate->handle($this, %s, \'\', false) ?>'."\n",
                var_export($options['virtual'], true)
            );
            ++$i;
            $chunks[$i] = str_replace($this->phpEscapeMap[0], $this->phpEscapeMap[1], $chunks[$i]);
            ++$i;
        }
        $content = implode('', $chunks);
        $response->setContent($content);
        $response->headers->set('X-Body-Eval', 'SSI');
        if ($response->headers->has('Surrogate-Control')) {
            $value = $response->headers->get('Surrogate-Control');
            if ('content="SSI/1.0"' == $value) {
                $response->headers->remove('Surrogate-Control');
            } elseif (preg_match('#,\s*content="SSI/1.0"#', $value)) {
                $response->headers->set('Surrogate-Control', preg_replace('#,\s*content="SSI/1.0"#', '', $value));
            } elseif (preg_match('#content="SSI/1.0",\s*#', $value)) {
                $response->headers->set('Surrogate-Control', preg_replace('#content="SSI/1.0",\s*#', '', $value));
            }
        }
    }
    public function handle(HttpCache $cache, $uri, $alt, $ignoreErrors)
    {
        $subRequest = Request::create($uri, 'get', array(), $cache->getRequest()->cookies->all(), array(), $cache->getRequest()->server->all());
        try {
            $response = $cache->handle($subRequest, HttpKernelInterface::SUB_REQUEST, true);
            if (!$response->isSuccessful()) {
                throw new \RuntimeException(sprintf('Error when rendering "%s" (Status code is %s).', $subRequest->getUri(), $response->getStatusCode()));
            }
            return $response->getContent();
        } catch (\Exception $e) {
            if ($alt) {
                return $this->handle($cache, $alt, '', $ignoreErrors);
            }
            if (!$ignoreErrors) {
                throw $e;
            }
        }
    }
}
