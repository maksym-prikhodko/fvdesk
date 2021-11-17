<?php
namespace Symfony\Component\HttpKernel\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
class Esi implements SurrogateInterface
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
        return 'esi';
    }
    public function createCacheStrategy()
    {
        return new EsiResponseCacheStrategy();
    }
    public function hasSurrogateCapability(Request $request)
    {
        return $this->hasSurrogateEsiCapability($request);
    }
    public function hasSurrogateEsiCapability(Request $request)
    {
        if (null === $value = $request->headers->get('Surrogate-Capability')) {
            return false;
        }
        return false !== strpos($value, 'ESI/1.0');
    }
    public function addSurrogateCapability(Request $request)
    {
        $this->addSurrogateEsiCapability($request);
    }
    public function addSurrogateEsiCapability(Request $request)
    {
        $current = $request->headers->get('Surrogate-Capability');
        $new = 'symfony2="ESI/1.0"';
        $request->headers->set('Surrogate-Capability', $current ? $current.', '.$new : $new);
    }
    public function addSurrogateControl(Response $response)
    {
        if (false !== strpos($response->getContent(), '<esi:include')) {
            $response->headers->set('Surrogate-Control', 'content="ESI/1.0"');
        }
    }
    public function needsParsing(Response $response)
    {
        return $this->needsEsiParsing($response);
    }
    public function needsEsiParsing(Response $response)
    {
        if (!$control = $response->headers->get('Surrogate-Control')) {
            return false;
        }
        return (bool) preg_match('#content="[^"]*ESI/1.0[^"]*"#', $control);
    }
    public function renderIncludeTag($uri, $alt = null, $ignoreErrors = true, $comment = '')
    {
        $html = sprintf('<esi:include src="%s"%s%s />',
            $uri,
            $ignoreErrors ? ' onerror="continue"' : '',
            $alt ? sprintf(' alt="%s"', $alt) : ''
        );
        if (!empty($comment)) {
            return sprintf("<esi:comment text=\"%s\" />\n%s", $comment, $html);
        }
        return $html;
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
        $content = preg_replace('#<esi\:remove>.*?</esi\:remove>#', '', $content);
        $content = preg_replace('#<esi\:comment[^>]*(?:/|</esi\:comment)>#', '', $content);
        $chunks = preg_split('#<esi\:include\s+(.*?)\s*(?:/|</esi\:include)>#', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $chunks[0] = str_replace($this->phpEscapeMap[0], $this->phpEscapeMap[1], $chunks[0]);
        $i = 1;
        while (isset($chunks[$i])) {
            $options = array();
            preg_match_all('/(src|onerror|alt)="([^"]*?)"/', $chunks[$i], $matches, PREG_SET_ORDER);
            foreach ($matches as $set) {
                $options[$set[1]] = $set[2];
            }
            if (!isset($options['src'])) {
                throw new \RuntimeException('Unable to process an ESI tag without a "src" attribute.');
            }
            $chunks[$i] = sprintf('<?php echo $this->surrogate->handle($this, %s, %s, %s) ?>'."\n",
                var_export($options['src'], true),
                var_export(isset($options['alt']) ? $options['alt'] : '', true),
                isset($options['onerror']) && 'continue' == $options['onerror'] ? 'true' : 'false'
            );
            ++$i;
            $chunks[$i] = str_replace($this->phpEscapeMap[0], $this->phpEscapeMap[1], $chunks[$i]);
            ++$i;
        }
        $content = implode('', $chunks);
        $response->setContent($content);
        $response->headers->set('X-Body-Eval', 'ESI');
        if ($response->headers->has('Surrogate-Control')) {
            $value = $response->headers->get('Surrogate-Control');
            if ('content="ESI/1.0"' == $value) {
                $response->headers->remove('Surrogate-Control');
            } elseif (preg_match('#,\s*content="ESI/1.0"#', $value)) {
                $response->headers->set('Surrogate-Control', preg_replace('#,\s*content="ESI/1.0"#', '', $value));
            } elseif (preg_match('#content="ESI/1.0",\s*#', $value)) {
                $response->headers->set('Surrogate-Control', preg_replace('#content="ESI/1.0",\s*#', '', $value));
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
