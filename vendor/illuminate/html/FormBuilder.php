<?php namespace Illuminate\Html;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Session\Store as Session;
use Illuminate\Support\Traits\Macroable;
class FormBuilder {
	use Macroable;
	protected $html;
	protected $url;
	protected $csrfToken;
	protected $session;
	protected $model;
	protected $labels = array();
	protected $reserved = array('method', 'url', 'route', 'action', 'files');
	protected $spoofedMethods = array('DELETE', 'PATCH', 'PUT');
	protected $skipValueTypes = array('file', 'password', 'checkbox', 'radio');
	public function __construct(HtmlBuilder $html, UrlGenerator $url, $csrfToken)
	{
		$this->url = $url;
		$this->html = $html;
		$this->csrfToken = $csrfToken;
	}
	public function open(array $options = array())
	{
		$method = array_get($options, 'method', 'post');
		$attributes['method'] = $this->getMethod($method);
		$attributes['action'] = $this->getAction($options);
		$attributes['accept-charset'] = 'UTF-8';
		$append = $this->getAppendage($method);
		if (isset($options['files']) && $options['files'])
		{
			$options['enctype'] = 'multipart/form-data';
		}
		$attributes = array_merge(
			$attributes, array_except($options, $this->reserved)
		);
		$attributes = $this->html->attributes($attributes);
		return '<form'.$attributes.'>'.$append;
	}
	public function model($model, array $options = array())
	{
		$this->model = $model;
		return $this->open($options);
	}
	public function setModel($model)
	{
		$this->model = $model;
	}
	public function close()
	{
		$this->labels = array();
		$this->model = null;
		return '</form>';
	}
	public function token()
	{
		$token = ! empty($this->csrfToken) ? $this->csrfToken : $this->session->getToken();
		return $this->hidden('_token', $token);
	}
	public function label($name, $value = null, $options = array())
	{
		$this->labels[] = $name;
		$options = $this->html->attributes($options);
		$value = e($this->formatLabel($name, $value));
		return '<label for="'.$name.'"'.$options.'>'.$value.'</label>';
	}
	protected function formatLabel($name, $value)
	{
		return $value ?: ucwords(str_replace('_', ' ', $name));
	}
	public function input($type, $name, $value = null, $options = array())
	{
		if ( ! isset($options['name'])) $options['name'] = $name;
		$id = $this->getIdAttribute($name, $options);
		if ( ! in_array($type, $this->skipValueTypes))
		{
			$value = $this->getValueAttribute($name, $value);
		}
		$merge = compact('type', 'value', 'id');
		$options = array_merge($options, $merge);
		return '<input'.$this->html->attributes($options).'>';
	}
	public function text($name, $value = null, $options = array())
	{
		return $this->input('text', $name, $value, $options);
	}
	public function password($name, $options = array())
	{
		return $this->input('password', $name, '', $options);
	}
	public function hidden($name, $value = null, $options = array())
	{
		return $this->input('hidden', $name, $value, $options);
	}
	public function email($name, $value = null, $options = array())
	{
		return $this->input('email', $name, $value, $options);
	}
	public function url($name, $value = null, $options = array())
	{
		return $this->input('url', $name, $value, $options);
	}
	public function file($name, $options = array())
	{
		return $this->input('file', $name, null, $options);
	}
	public function textarea($name, $value = null, $options = array())
	{
		if ( ! isset($options['name'])) $options['name'] = $name;
		$options = $this->setTextAreaSize($options);
		$options['id'] = $this->getIdAttribute($name, $options);
		$value = (string) $this->getValueAttribute($name, $value);
		unset($options['size']);
		$options = $this->html->attributes($options);
		return '<textarea'.$options.'>'.e($value).'</textarea>';
	}
	protected function setTextAreaSize($options)
	{
		if (isset($options['size']))
		{
			return $this->setQuickTextAreaSize($options);
		}
		$cols = array_get($options, 'cols', 50);
		$rows = array_get($options, 'rows', 10);
		return array_merge($options, compact('cols', 'rows'));
	}
	protected function setQuickTextAreaSize($options)
	{
		$segments = explode('x', $options['size']);
		return array_merge($options, array('cols' => $segments[0], 'rows' => $segments[1]));
	}
	public function select($name, $list = array(), $selected = null, $options = array())
	{
		$selected = $this->getValueAttribute($name, $selected);
		$options['id'] = $this->getIdAttribute($name, $options);
		if ( ! isset($options['name'])) $options['name'] = $name;
		$html = array();
		foreach ($list as $value => $display)
		{
			$html[] = $this->getSelectOption($display, $value, $selected);
		}
		$options = $this->html->attributes($options);
		$list = implode('', $html);
		return "<select{$options}>{$list}</select>";
	}
	public function selectRange($name, $begin, $end, $selected = null, $options = array())
	{
		$range = array_combine($range = range($begin, $end), $range);
		return $this->select($name, $range, $selected, $options);
	}
	public function selectYear()
	{
		return call_user_func_array(array($this, 'selectRange'), func_get_args());
	}
	public function selectMonth($name, $selected = null, $options = array(), $format = '%B')
	{
		$months = array();
		foreach (range(1, 12) as $month)
		{
			$months[$month] = strftime($format, mktime(0, 0, 0, $month, 1));
		}
		return $this->select($name, $months, $selected, $options);
	}
	public function getSelectOption($display, $value, $selected)
	{
		if (is_array($display))
		{
			return $this->optionGroup($display, $value, $selected);
		}
		return $this->option($display, $value, $selected);
	}
	protected function optionGroup($list, $label, $selected)
	{
		$html = array();
		foreach ($list as $value => $display)
		{
			$html[] = $this->option($display, $value, $selected);
		}
		return '<optgroup label="'.e($label).'">'.implode('', $html).'</optgroup>';
	}
	protected function option($display, $value, $selected)
	{
		$selected = $this->getSelectedValue($value, $selected);
		$options = array('value' => e($value), 'selected' => $selected);
		return '<option'.$this->html->attributes($options).'>'.e($display).'</option>';
	}
	protected function getSelectedValue($value, $selected)
	{
		if (is_array($selected))
		{
			return in_array($value, $selected) ? 'selected' : null;
		}
		return ((string) $value == (string) $selected) ? 'selected' : null;
	}
	public function checkbox($name, $value = 1, $checked = null, $options = array())
	{
		return $this->checkable('checkbox', $name, $value, $checked, $options);
	}
	public function radio($name, $value = null, $checked = null, $options = array())
	{
		if (is_null($value)) $value = $name;
		return $this->checkable('radio', $name, $value, $checked, $options);
	}
	protected function checkable($type, $name, $value, $checked, $options)
	{
		$checked = $this->getCheckedState($type, $name, $value, $checked);
		if ($checked) $options['checked'] = 'checked';
		return $this->input($type, $name, $value, $options);
	}
	protected function getCheckedState($type, $name, $value, $checked)
	{
		switch ($type)
		{
			case 'checkbox':
				return $this->getCheckboxCheckedState($name, $value, $checked);
			case 'radio':
				return $this->getRadioCheckedState($name, $value, $checked);
			default:
				return $this->getValueAttribute($name) == $value;
		}
	}
	protected function getCheckboxCheckedState($name, $value, $checked)
	{
		if (isset($this->session) && ! $this->oldInputIsEmpty() && is_null($this->old($name))) return false;
		if ($this->missingOldAndModel($name)) return $checked;
		$posted = $this->getValueAttribute($name);
		return is_array($posted) ? in_array($value, $posted) : (bool) $posted;
	}
	protected function getRadioCheckedState($name, $value, $checked)
	{
		if ($this->missingOldAndModel($name)) return $checked;
		return $this->getValueAttribute($name) == $value;
	}
	protected function missingOldAndModel($name)
	{
		return (is_null($this->old($name)) && is_null($this->getModelValueAttribute($name)));
	}
	public function reset($value, $attributes = array())
	{
		return $this->input('reset', null, $value, $attributes);
	}
	public function image($url, $name = null, $attributes = array())
	{
		$attributes['src'] = $this->url->asset($url);
		return $this->input('image', $name, null, $attributes);
	}
	public function submit($value = null, $options = array())
	{
		return $this->input('submit', null, $value, $options);
	}
	public function button($value = null, $options = array())
	{
		if ( ! array_key_exists('type', $options))
		{
			$options['type'] = 'button';
		}
		return '<button'.$this->html->attributes($options).'>'.$value.'</button>';
	}
	protected function getMethod($method)
	{
		$method = strtoupper($method);
		return $method != 'GET' ? 'POST' : $method;
	}
	protected function getAction(array $options)
	{
		if (isset($options['url']))
		{
			return $this->getUrlAction($options['url']);
		}
		if (isset($options['route']))
		{
			return $this->getRouteAction($options['route']);
		}
		elseif (isset($options['action']))
		{
			return $this->getControllerAction($options['action']);
		}
		return $this->url->current();
	}
	protected function getUrlAction($options)
	{
		if (is_array($options))
		{
			return $this->url->to($options[0], array_slice($options, 1));
		}
		return $this->url->to($options);
	}
	protected function getRouteAction($options)
	{
		if (is_array($options))
		{
			return $this->url->route($options[0], array_slice($options, 1));
		}
		return $this->url->route($options);
	}
	protected function getControllerAction($options)
	{
		if (is_array($options))
		{
			return $this->url->action($options[0], array_slice($options, 1));
		}
		return $this->url->action($options);
	}
	protected function getAppendage($method)
	{
		list($method, $appendage) = array(strtoupper($method), '');
		if (in_array($method, $this->spoofedMethods))
		{
			$appendage .= $this->hidden('_method', $method);
		}
		if ($method != 'GET')
		{
			$appendage .= $this->token();
		}
		return $appendage;
	}
	public function getIdAttribute($name, $attributes)
	{
		if (array_key_exists('id', $attributes))
		{
			return $attributes['id'];
		}
		if (in_array($name, $this->labels))
		{
			return $name;
		}
	}
	public function getValueAttribute($name, $value = null)
	{
		if (is_null($name)) return $value;
		if ( ! is_null($this->old($name)))
		{
			return $this->old($name);
		}
		if ( ! is_null($value)) return $value;
		if (isset($this->model))
		{
			return $this->getModelValueAttribute($name);
		}
	}
	protected function getModelValueAttribute($name)
	{
		if (is_object($this->model))
		{
			return object_get($this->model, $this->transformKey($name));
		}
		elseif (is_array($this->model))
		{
			return array_get($this->model, $this->transformKey($name));
		}
	}
	public function old($name)
	{
		if (isset($this->session))
		{
			return $this->session->getOldInput($this->transformKey($name));
		}
	}
	public function oldInputIsEmpty()
	{
		return (isset($this->session) && count($this->session->getOldInput()) == 0);
	}
	protected function transformKey($key)
	{
		return str_replace(array('.', '[]', '[', ']'), array('_', '', '.', ''), $key);
	}
	public function getSessionStore()
	{
		return $this->session;
	}
	public function setSessionStore(Session $session)
	{
		$this->session = $session;
		return $this;
	}
}
