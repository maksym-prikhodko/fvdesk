<?php
namespace Symfony\Component\HttpFoundation;
class JsonResponse extends Response
{
    protected $data;
    protected $callback;
    protected $encodingOptions;
    public function __construct($data = null, $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);
        if (null === $data) {
            $data = new \ArrayObject();
        }
        $this->encodingOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
        $this->setData($data);
    }
    public static function create($data = null, $status = 200, $headers = array())
    {
        return new static($data, $status, $headers);
    }
    public function setCallback($callback = null)
    {
        if (null !== $callback) {
            $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';
            $parts = explode('.', $callback);
            foreach ($parts as $part) {
                if (!preg_match($pattern, $part)) {
                    throw new \InvalidArgumentException('The callback name is not valid.');
                }
            }
        }
        $this->callback = $callback;
        return $this->update();
    }
    public function setData($data = array())
    {
        $errorHandler = null;
        $errorHandler = set_error_handler(function () use (&$errorHandler) {
            if (JSON_ERROR_NONE !== json_last_error()) {
                return;
            }
            if ($errorHandler) {
                call_user_func_array($errorHandler, func_get_args());
            }
        });
        try {
            json_encode(null);
            $this->data = json_encode($data, $this->encodingOptions);
            restore_error_handler();
        } catch (\Exception $exception) {
            restore_error_handler();
            throw $exception;
        }
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException($this->transformJsonError());
        }
        return $this->update();
    }
    public function getEncodingOptions()
    {
        return $this->encodingOptions;
    }
    public function setEncodingOptions($encodingOptions)
    {
        $this->encodingOptions = (int) $encodingOptions;
        return $this->setData(json_decode($this->data));
    }
    protected function update()
    {
        if (null !== $this->callback) {
            $this->headers->set('Content-Type', 'text/javascript');
            return $this->setContent(sprintf('%s(%s);', $this->callback, $this->data));
        }
        if (!$this->headers->has('Content-Type') || 'text/javascript' === $this->headers->get('Content-Type')) {
            $this->headers->set('Content-Type', 'application/json');
        }
        return $this->setContent($this->data);
    }
    private function transformJsonError()
    {
        if (function_exists('json_last_error_msg')) {
            return json_last_error_msg();
        }
        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded.';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch.';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found.';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON.';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            default:
                return 'Unknown error.';
        }
    }
}
