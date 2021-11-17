<?php namespace Illuminate\Translation;
use Illuminate\Support\Collection;
use Illuminate\Support\NamespacedItemResolver;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\TranslatorInterface;
class Translator extends NamespacedItemResolver implements TranslatorInterface {
	protected $loader;
	protected $locale;
	protected $fallback;
	protected $loaded = array();
	public function __construct(LoaderInterface $loader, $locale)
	{
		$this->loader = $loader;
		$this->locale = $locale;
	}
	public function has($key, $locale = null)
	{
		return $this->get($key, array(), $locale) !== $key;
	}
	public function get($key, array $replace = array(), $locale = null)
	{
		list($namespace, $group, $item) = $this->parseKey($key);
		foreach ($this->parseLocale($locale) as $locale)
		{
			$this->load($namespace, $group, $locale);
			$line = $this->getLine(
				$namespace, $group, $locale, $item, $replace
			);
			if ( ! is_null($line)) break;
		}
		if ( ! isset($line)) return $key;
		return $line;
	}
	protected function getLine($namespace, $group, $locale, $item, array $replace)
	{
		$line = array_get($this->loaded[$namespace][$group][$locale], $item);
		if (is_string($line))
		{
			return $this->makeReplacements($line, $replace);
		}
		elseif (is_array($line) && count($line) > 0)
		{
			return $line;
		}
	}
	protected function makeReplacements($line, array $replace)
	{
		$replace = $this->sortReplacements($replace);
		foreach ($replace as $key => $value)
		{
			$line = str_replace(':'.$key, $value, $line);
		}
		return $line;
	}
	protected function sortReplacements(array $replace)
	{
		return (new Collection($replace))->sortBy(function($value, $key)
		{
			return mb_strlen($key) * -1;
		});
	}
	public function choice($key, $number, array $replace = array(), $locale = null)
	{
		$line = $this->get($key, $replace, $locale = $locale ?: $this->locale ?: $this->fallback);
		$replace['count'] = $number;
		return $this->makeReplacements($this->getSelector()->choose($line, $number, $locale), $replace);
	}
	public function trans($id, array $parameters = array(), $domain = 'messages', $locale = null)
	{
		return $this->get($id, $parameters, $locale);
	}
	public function transChoice($id, $number, array $parameters = array(), $domain = 'messages', $locale = null)
	{
		return $this->choice($id, $number, $parameters, $locale);
	}
	public function load($namespace, $group, $locale)
	{
		if ($this->isLoaded($namespace, $group, $locale)) return;
		$lines = $this->loader->load($locale, $group, $namespace);
		$this->loaded[$namespace][$group][$locale] = $lines;
	}
	protected function isLoaded($namespace, $group, $locale)
	{
		return isset($this->loaded[$namespace][$group][$locale]);
	}
	public function addNamespace($namespace, $hint)
	{
		$this->loader->addNamespace($namespace, $hint);
	}
	public function parseKey($key)
	{
		$segments = parent::parseKey($key);
		if (is_null($segments[0])) $segments[0] = '*';
		return $segments;
	}
	protected function parseLocale($locale)
	{
		if ( ! is_null($locale))
		{
			return array_filter(array($locale, $this->fallback));
		}
		return array_filter(array($this->locale, $this->fallback));
	}
	public function getSelector()
	{
		if ( ! isset($this->selector))
		{
			$this->selector = new MessageSelector;
		}
		return $this->selector;
	}
	public function setSelector(MessageSelector $selector)
	{
		$this->selector = $selector;
	}
	public function getLoader()
	{
		return $this->loader;
	}
	public function locale()
	{
		return $this->getLocale();
	}
	public function getLocale()
	{
		return $this->locale;
	}
	public function setLocale($locale)
	{
		$this->locale = $locale;
	}
	public function getFallback()
	{
		return $this->fallback;
	}
	public function setFallback($fallback)
	{
		$this->fallback = $fallback;
	}
}
