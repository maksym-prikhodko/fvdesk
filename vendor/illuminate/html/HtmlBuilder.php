<?php namespace Illuminate\Html;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Traits\Macroable;
class HtmlBuilder {
	use Macroable;
	protected $url;
	public function __construct(UrlGenerator $url = null)
	{
		$this->url = $url;
	}
	public function entities($value)
	{
		return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
	}
	public function decode($value)
	{
		return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
	}
	public function script($url, $attributes = array(), $secure = null)
	{
		$attributes['src'] = $this->url->asset($url, $secure);
		return '<script'.$this->attributes($attributes).'></script>'.PHP_EOL;
	}
	public function style($url, $attributes = array(), $secure = null)
	{
		$defaults = array('media' => 'all', 'type' => 'text/css', 'rel' => 'stylesheet');
		$attributes = $attributes + $defaults;
		$attributes['href'] = $this->url->asset($url, $secure);
		return '<link'.$this->attributes($attributes).'>'.PHP_EOL;
	}
	public function image($url, $alt = null, $attributes = array(), $secure = null)
	{
		$attributes['alt'] = $alt;
		return '<img src="'.$this->url->asset($url, $secure).'"'.$this->attributes($attributes).'>';
	}
	public function link($url, $title = null, $attributes = array(), $secure = null)
	{
		$url = $this->url->to($url, array(), $secure);
		if (is_null($title) || $title === false) $title = $url;
		return '<a href="'.$url.'"'.$this->attributes($attributes).'>'.$this->entities($title).'</a>';
	}
	public function secureLink($url, $title = null, $attributes = array())
	{
		return $this->link($url, $title, $attributes, true);
	}
	public function linkAsset($url, $title = null, $attributes = array(), $secure = null)
	{
		$url = $this->url->asset($url, $secure);
		return $this->link($url, $title ?: $url, $attributes, $secure);
	}
	public function linkSecureAsset($url, $title = null, $attributes = array())
	{
		return $this->linkAsset($url, $title, $attributes, true);
	}
	public function linkRoute($name, $title = null, $parameters = array(), $attributes = array())
	{
		return $this->link($this->url->route($name, $parameters), $title, $attributes);
	}
	public function linkAction($action, $title = null, $parameters = array(), $attributes = array())
	{
		return $this->link($this->url->action($action, $parameters), $title, $attributes);
	}
	public function mailto($email, $title = null, $attributes = array())
	{
		$email = $this->email($email);
		$title = $title ?: $email;
		$email = $this->obfuscate('mailto:') . $email;
		return '<a href="'.$email.'"'.$this->attributes($attributes).'>'.$this->entities($title).'</a>';
	}
	public function email($email)
	{
		return str_replace('@', '&#64;', $this->obfuscate($email));
	}
	public function ol($list, $attributes = array())
	{
		return $this->listing('ol', $list, $attributes);
	}
	public function ul($list, $attributes = array())
	{
		return $this->listing('ul', $list, $attributes);
	}
	protected function listing($type, $list, $attributes = array())
	{
		$html = '';
		if (count($list) == 0) return $html;
		foreach ($list as $key => $value)
		{
			$html .= $this->listingElement($key, $type, $value);
		}
		$attributes = $this->attributes($attributes);
		return "<{$type}{$attributes}>{$html}</{$type}>";
	}
	protected function listingElement($key, $type, $value)
	{
		if (is_array($value))
		{
			return $this->nestedListing($key, $type, $value);
		}
		else
		{
			return '<li>'.e($value).'</li>';
		}
	}
	protected function nestedListing($key, $type, $value)
	{
		if (is_int($key))
		{
			return $this->listing($type, $value);
		}
		else
		{
			return '<li>'.$key.$this->listing($type, $value).'</li>';
		}
	}
	public function attributes($attributes)
	{
		$html = array();
		foreach ((array) $attributes as $key => $value)
		{
			$element = $this->attributeElement($key, $value);
			if ( ! is_null($element)) $html[] = $element;
		}
		return count($html) > 0 ? ' '.implode(' ', $html) : '';
	}
	protected function attributeElement($key, $value)
	{
		if (is_numeric($key)) $key = $value;
		if ( ! is_null($value)) return $key.'="'.e($value).'"';
	}
	public function obfuscate($value)
	{
		$safe = '';
		foreach (str_split($value) as $letter)
		{
			if (ord($letter) > 128) return $letter;
			switch (rand(1, 3))
			{
				case 1:
					$safe .= '&#'.ord($letter).';'; break;
				case 2:
					$safe .= '&#x'.dechex(ord($letter)).';'; break;
				case 3:
					$safe .= $letter;
			}
		}
		return $safe;
	}
}
