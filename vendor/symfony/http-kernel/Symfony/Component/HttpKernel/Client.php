<?php
namespace Symfony\Component\HttpKernel;
use Symfony\Component\BrowserKit\Client as BaseClient;
use Symfony\Component\BrowserKit\Request as DomRequest;
use Symfony\Component\BrowserKit\Response as DomResponse;
use Symfony\Component\BrowserKit\Cookie as DomCookie;
use Symfony\Component\BrowserKit\History;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class Client extends BaseClient
{
    protected $kernel;
    public function __construct(HttpKernelInterface $kernel, array $server = array(), History $history = null, CookieJar $cookieJar = null)
    {
        $this->kernel = $kernel;
        $this->followRedirects = false;
        parent::__construct($server, $history, $cookieJar);
    }
    public function getRequest()
    {
        return parent::getRequest();
    }
    public function getResponse()
    {
        return parent::getResponse();
    }
    protected function doRequest($request)
    {
        $response = $this->kernel->handle($request);
        if ($this->kernel instanceof TerminableInterface) {
            $this->kernel->terminate($request, $response);
        }
        return $response;
    }
    protected function getScript($request)
    {
        $kernel = str_replace("'", "\\'", serialize($this->kernel));
        $request = str_replace("'", "\\'", serialize($request));
        $r = new \ReflectionClass('\\Symfony\\Component\\ClassLoader\\ClassLoader');
        $requirePath = str_replace("'", "\\'", $r->getFileName());
        $symfonyPath = str_replace("'", "\\'", realpath(__DIR__.'/../../..'));
        $errorReporting = error_reporting();
        $code = <<<EOF
<?php
error_reporting($errorReporting & ~E_USER_DEPRECATED);
require_once '$requirePath';
\$loader = new Symfony\Component\ClassLoader\ClassLoader();
\$loader->addPrefix('Symfony', '$symfonyPath');
\$loader->register();
\$kernel = unserialize('$kernel');
\$request = unserialize('$request');
EOF;
        return $code.$this->getHandleScript();
    }
    protected function getHandleScript()
    {
        return <<<'EOF'
$response = $kernel->handle($request);
if ($kernel instanceof Symfony\Component\HttpKernel\TerminableInterface) {
    $kernel->terminate($request, $response);
}
echo serialize($response);
EOF;
    }
    protected function filterRequest(DomRequest $request)
    {
        $httpRequest = Request::create($request->getUri(), $request->getMethod(), $request->getParameters(), $request->getCookies(), $request->getFiles(), $request->getServer(), $request->getContent());
        foreach ($this->filterFiles($httpRequest->files->all()) as $key => $value) {
            $httpRequest->files->set($key, $value);
        }
        return $httpRequest;
    }
    protected function filterFiles(array $files)
    {
        $filtered = array();
        foreach ($files as $key => $value) {
            if (is_array($value)) {
                $filtered[$key] = $this->filterFiles($value);
            } elseif ($value instanceof UploadedFile) {
                if ($value->isValid() && $value->getSize() > UploadedFile::getMaxFilesize()) {
                    $filtered[$key] = new UploadedFile(
                        '',
                        $value->getClientOriginalName(),
                        $value->getClientMimeType(),
                        0,
                        UPLOAD_ERR_INI_SIZE,
                        true
                    );
                } else {
                    $filtered[$key] = new UploadedFile(
                        $value->getPathname(),
                        $value->getClientOriginalName(),
                        $value->getClientMimeType(),
                        $value->getClientSize(),
                        $value->getError(),
                        true
                    );
                }
            }
        }
        return $filtered;
    }
    protected function filterResponse($response)
    {
        $headers = $response->headers->all();
        if ($response->headers->getCookies()) {
            $cookies = array();
            foreach ($response->headers->getCookies() as $cookie) {
                $cookies[] = new DomCookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
            }
            $headers['Set-Cookie'] = $cookies;
        }
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();
        return new DomResponse($content, $response->getStatusCode(), $headers);
    }
}
