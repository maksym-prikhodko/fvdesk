<?php namespace Illuminate\Validation;
use Closure;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\Translation\TranslatorInterface;
use Illuminate\Contracts\Validation\Factory as FactoryContract;
class Factory implements FactoryContract {
	protected $translator;
	protected $verifier;
	protected $container;
	protected $extensions = array();
	protected $implicitExtensions = array();
	protected $replacers = array();
	protected $fallbackMessages = array();
	protected $resolver;
	public function __construct(TranslatorInterface $translator, Container $container = null)
	{
		$this->container = $container;
		$this->translator = $translator;
	}
	public function make(array $data, array $rules, array $messages = array(), array $customAttributes = array())
	{
		$validator = $this->resolve($data, $rules, $messages, $customAttributes);
		if ( ! is_null($this->verifier))
		{
			$validator->setPresenceVerifier($this->verifier);
		}
		if ( ! is_null($this->container))
		{
			$validator->setContainer($this->container);
		}
		$this->addExtensions($validator);
		return $validator;
	}
	protected function addExtensions(Validator $validator)
	{
		$validator->addExtensions($this->extensions);
		$implicit = $this->implicitExtensions;
		$validator->addImplicitExtensions($implicit);
		$validator->addReplacers($this->replacers);
		$validator->setFallbackMessages($this->fallbackMessages);
	}
	protected function resolve(array $data, array $rules, array $messages, array $customAttributes)
	{
		if (is_null($this->resolver))
		{
			return new Validator($this->translator, $data, $rules, $messages, $customAttributes);
		}
		return call_user_func($this->resolver, $this->translator, $data, $rules, $messages, $customAttributes);
	}
	public function extend($rule, $extension, $message = null)
	{
		$this->extensions[$rule] = $extension;
		if ($message) $this->fallbackMessages[snake_case($rule)] = $message;
	}
	public function extendImplicit($rule, $extension, $message = null)
	{
		$this->implicitExtensions[$rule] = $extension;
		if ($message) $this->fallbackMessages[snake_case($rule)] = $message;
	}
	public function replacer($rule, $replacer)
	{
		$this->replacers[$rule] = $replacer;
	}
	public function resolver(Closure $resolver)
	{
		$this->resolver = $resolver;
	}
	public function getTranslator()
	{
		return $this->translator;
	}
	public function getPresenceVerifier()
	{
		return $this->verifier;
	}
	public function setPresenceVerifier(PresenceVerifierInterface $presenceVerifier)
	{
		$this->verifier = $presenceVerifier;
	}
}
