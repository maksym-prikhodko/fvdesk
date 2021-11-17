<?php namespace Illuminate\Validation;
use Closure;
use DateTime;
use Countable;
use Exception;
use DateTimeZone;
use RuntimeException;
use BadMethodCallException;
use InvalidArgumentException;
use Illuminate\Support\Fluent;
use Illuminate\Support\MessageBag;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
class Validator implements ValidatorContract {
	protected $translator;
	protected $presenceVerifier;
	protected $container;
	protected $failedRules = array();
	protected $messages;
	protected $data;
	protected $files = array();
	protected $rules;
	protected $after = array();
	protected $customMessages = array();
	protected $fallbackMessages = array();
	protected $customAttributes = array();
	protected $customValues = array();
	protected $extensions = array();
	protected $replacers = array();
	protected $sizeRules = array('Size', 'Between', 'Min', 'Max');
	protected $numericRules = array('Numeric', 'Integer');
	protected $implicitRules = array(
		'Required', 'RequiredWith', 'RequiredWithAll', 'RequiredWithout', 'RequiredWithoutAll', 'RequiredIf', 'Accepted',
	);
	public function __construct(TranslatorInterface $translator, array $data, array $rules, array $messages = array(), array $customAttributes = array())
	{
		$this->translator = $translator;
		$this->customMessages = $messages;
		$this->data = $this->parseData($data);
		$this->rules = $this->explodeRules($rules);
		$this->customAttributes = $customAttributes;
	}
	protected function parseData(array $data, $arrayKey = null)
	{
		if (is_null($arrayKey))
		{
			$this->files = array();
		}
		foreach ($data as $key => $value)
		{
			$key = ($arrayKey) ? "$arrayKey.$key" : $key;
			if ($value instanceof File)
			{
				$this->files[$key] = $value;
				unset($data[$key]);
			}
			elseif (is_array($value))
			{
				$this->parseData($value, $key);
			}
		}
		return $data;
	}
	protected function explodeRules($rules)
	{
		foreach ($rules as $key => &$rule)
		{
			$rule = (is_string($rule)) ? explode('|', $rule) : $rule;
		}
		return $rules;
	}
	public function after($callback)
	{
		$this->after[] = function() use ($callback)
		{
			return call_user_func_array($callback, [$this]);
		};
		return $this;
	}
	public function sometimes($attribute, $rules, callable $callback)
	{
		$payload = new Fluent(array_merge($this->data, $this->files));
		if (call_user_func($callback, $payload))
		{
			foreach ((array) $attribute as $key)
			{
				$this->mergeRules($key, $rules);
			}
		}
	}
	public function each($attribute, $rules)
	{
		$data = array_get($this->data, $attribute);
		if ( ! is_array($data))
		{
			if ($this->hasRule($attribute, 'Array')) return;
			throw new InvalidArgumentException('Attribute for each() must be an array.');
		}
		foreach ($data as $dataKey => $dataValue)
		{
			foreach ($rules as $ruleKey => $ruleValue)
			{
				if ( ! is_string($ruleKey))
				{
					$this->mergeRules("$attribute.$dataKey", $ruleValue);
				}
				else
				{
					$this->mergeRules("$attribute.$dataKey.$ruleKey", $ruleValue);
				}
			}
		}
	}
	public function mergeRules($attribute, $rules)
	{
		$current = isset($this->rules[$attribute]) ? $this->rules[$attribute] : [];
		$merge = head($this->explodeRules(array($rules)));
		$this->rules[$attribute] = array_merge($current, $merge);
	}
	public function passes()
	{
		$this->messages = new MessageBag;
		foreach ($this->rules as $attribute => $rules)
		{
			foreach ($rules as $rule)
			{
				$this->validate($attribute, $rule);
			}
		}
		foreach ($this->after as $after)
		{
			call_user_func($after);
		}
		return count($this->messages->all()) === 0;
	}
	public function fails()
	{
		return ! $this->passes();
	}
	protected function validate($attribute, $rule)
	{
		list($rule, $parameters) = $this->parseRule($rule);
		if ($rule == '') return;
		$value = $this->getValue($attribute);
		$validatable = $this->isValidatable($rule, $attribute, $value);
		$method = "validate{$rule}";
		if ($validatable && ! $this->$method($attribute, $value, $parameters, $this))
		{
			$this->addFailure($attribute, $rule, $parameters);
		}
	}
	public function valid()
	{
		if ( ! $this->messages) $this->passes();
		return array_diff_key($this->data, $this->messages()->toArray());
	}
	public function invalid()
	{
		if ( ! $this->messages) $this->passes();
		return array_intersect_key($this->data, $this->messages()->toArray());
	}
	protected function getValue($attribute)
	{
		if ( ! is_null($value = array_get($this->data, $attribute)))
		{
			return $value;
		}
		elseif ( ! is_null($value = array_get($this->files, $attribute)))
		{
			return $value;
		}
	}
	protected function isValidatable($rule, $attribute, $value)
	{
		return $this->presentOrRuleIsImplicit($rule, $attribute, $value) &&
               $this->passesOptionalCheck($attribute) &&
               $this->hasNotFailedPreviousRuleIfPresenceRule($rule, $attribute);
	}
	protected function presentOrRuleIsImplicit($rule, $attribute, $value)
	{
		return $this->validateRequired($attribute, $value) || $this->isImplicit($rule);
	}
	protected function passesOptionalCheck($attribute)
	{
		if ($this->hasRule($attribute, array('Sometimes')))
		{
			return array_key_exists($attribute, array_dot($this->data))
				|| in_array($attribute, array_keys($this->data))
				|| array_key_exists($attribute, $this->files);
		}
		return true;
	}
	protected function isImplicit($rule)
	{
		return in_array($rule, $this->implicitRules);
	}
	protected function hasNotFailedPreviousRuleIfPresenceRule($rule, $attribute)
	{
		return in_array($rule, ['Unique', 'Exists'])
						? ! $this->messages->has($attribute) : true;
	}
	protected function addFailure($attribute, $rule, $parameters)
	{
		$this->addError($attribute, $rule, $parameters);
		$this->failedRules[$attribute][$rule] = $parameters;
	}
	protected function addError($attribute, $rule, $parameters)
	{
		$message = $this->getMessage($attribute, $rule);
		$message = $this->doReplacements($message, $attribute, $rule, $parameters);
		$this->messages->add($attribute, $message);
	}
	protected function validateSometimes()
	{
		return true;
	}
	protected function validateRequired($attribute, $value)
	{
		if (is_null($value))
		{
			return false;
		}
		elseif (is_string($value) && trim($value) === '')
		{
			return false;
		}
		elseif ((is_array($value) || $value instanceof Countable) && count($value) < 1)
		{
			return false;
		}
		elseif ($value instanceof File)
		{
			return (string) $value->getPath() != '';
		}
		return true;
	}
	protected function validateFilled($attribute, $value)
	{
		if (array_key_exists($attribute, $this->data) || array_key_exists($attribute, $this->files))
		{
			return $this->validateRequired($attribute, $value);
		}
		return true;
	}
	protected function anyFailingRequired(array $attributes)
	{
		foreach ($attributes as $key)
		{
			if ( ! $this->validateRequired($key, $this->getValue($key)))
			{
				return true;
			}
		}
		return false;
	}
	protected function allFailingRequired(array $attributes)
	{
		foreach ($attributes as $key)
		{
			if ($this->validateRequired($key, $this->getValue($key)))
			{
				return false;
			}
		}
		return true;
	}
	protected function validateRequiredWith($attribute, $value, $parameters)
	{
		if ( ! $this->allFailingRequired($parameters))
		{
			return $this->validateRequired($attribute, $value);
		}
		return true;
	}
	protected function validateRequiredWithAll($attribute, $value, $parameters)
	{
		if ( ! $this->anyFailingRequired($parameters))
		{
			return $this->validateRequired($attribute, $value);
		}
		return true;
	}
	protected function validateRequiredWithout($attribute, $value, $parameters)
	{
		if ($this->anyFailingRequired($parameters))
		{
			return $this->validateRequired($attribute, $value);
		}
		return true;
	}
	protected function validateRequiredWithoutAll($attribute, $value, $parameters)
	{
		if ($this->allFailingRequired($parameters))
		{
			return $this->validateRequired($attribute, $value);
		}
		return true;
	}
	protected function validateRequiredIf($attribute, $value, $parameters)
	{
		$this->requireParameterCount(2, $parameters, 'required_if');
		$data = array_get($this->data, $parameters[0]);
		$values = array_slice($parameters, 1);
		if (in_array($data, $values))
		{
			return $this->validateRequired($attribute, $value);
		}
		return true;
	}
	protected function getPresentCount($attributes)
	{
		$count = 0;
		foreach ($attributes as $key)
		{
			if (array_get($this->data, $key) || array_get($this->files, $key))
			{
				$count++;
			}
		}
		return $count;
	}
	protected function validateConfirmed($attribute, $value)
	{
		return $this->validateSame($attribute, $value, array($attribute.'_confirmation'));
	}
	protected function validateSame($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'same');
		$other = array_get($this->data, $parameters[0]);
		return isset($other) && $value == $other;
	}
	protected function validateDifferent($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'different');
		$other = array_get($this->data, $parameters[0]);
		return isset($other) && $value != $other;
	}
	protected function validateAccepted($attribute, $value)
	{
		$acceptable = array('yes', 'on', '1', 1, true, 'true');
		return $this->validateRequired($attribute, $value) && in_array($value, $acceptable, true);
	}
	protected function validateArray($attribute, $value)
	{
		return is_array($value);
	}
	protected function validateBoolean($attribute, $value)
	{
		$acceptable = array(true, false, 0, 1, '0', '1');
		return in_array($value, $acceptable, true);
	}
	protected function validateInteger($attribute, $value)
	{
		return filter_var($value, FILTER_VALIDATE_INT) !== false;
	}
	protected function validateNumeric($attribute, $value)
	{
		return is_numeric($value);
	}
	protected function validateString($attribute, $value)
	{
		return is_string($value);
	}
	protected function validateDigits($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'digits');
		return $this->validateNumeric($attribute, $value)
			&& strlen((string) $value) == $parameters[0];
	}
	protected function validateDigitsBetween($attribute, $value, $parameters)
	{
		$this->requireParameterCount(2, $parameters, 'digits_between');
		$length = strlen((string) $value);
		return $this->validateNumeric($attribute, $value)
		  && $length >= $parameters[0] && $length <= $parameters[1];
	}
	protected function validateSize($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'size');
		return $this->getSize($attribute, $value) == $parameters[0];
	}
	protected function validateBetween($attribute, $value, $parameters)
	{
		$this->requireParameterCount(2, $parameters, 'between');
		$size = $this->getSize($attribute, $value);
		return $size >= $parameters[0] && $size <= $parameters[1];
	}
	protected function validateMin($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'min');
		return $this->getSize($attribute, $value) >= $parameters[0];
	}
	protected function validateMax($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'max');
		if ($value instanceof UploadedFile && ! $value->isValid()) return false;
		return $this->getSize($attribute, $value) <= $parameters[0];
	}
	protected function getSize($attribute, $value)
	{
		$hasNumeric = $this->hasRule($attribute, $this->numericRules);
		if (is_numeric($value) && $hasNumeric)
		{
			return array_get($this->data, $attribute);
		}
		elseif (is_array($value))
		{
			return count($value);
		}
		elseif ($value instanceof File)
		{
			return $value->getSize() / 1024;
		}
		return mb_strlen($value);
	}
	protected function validateIn($attribute, $value, $parameters)
	{
		return in_array((string) $value, $parameters);
	}
	protected function validateNotIn($attribute, $value, $parameters)
	{
		return ! $this->validateIn($attribute, $value, $parameters);
	}
	protected function validateUnique($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'unique');
		$table = $parameters[0];
		$column = isset($parameters[1]) ? $parameters[1] : $attribute;
		list($idColumn, $id) = array(null, null);
		if (isset($parameters[2]))
		{
			list($idColumn, $id) = $this->getUniqueIds($parameters);
			if (strtolower($id) == 'null') $id = null;
		}
		$verifier = $this->getPresenceVerifier();
		$extra = $this->getUniqueExtra($parameters);
		return $verifier->getCount(
			$table, $column, $value, $id, $idColumn, $extra
		) == 0;
	}
	protected function getUniqueIds($parameters)
	{
		$idColumn = isset($parameters[3]) ? $parameters[3] : 'id';
		return array($idColumn, $parameters[2]);
	}
	protected function getUniqueExtra($parameters)
	{
		if (isset($parameters[4]))
		{
			return $this->getExtraConditions(array_slice($parameters, 4));
		}
		return array();
	}
	protected function validateExists($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'exists');
		$table = $parameters[0];
		$column = isset($parameters[1]) ? $parameters[1] : $attribute;
		$expected = (is_array($value)) ? count($value) : 1;
		return $this->getExistCount($table, $column, $value, $parameters) >= $expected;
	}
	protected function getExistCount($table, $column, $value, $parameters)
	{
		$verifier = $this->getPresenceVerifier();
		$extra = $this->getExtraExistConditions($parameters);
		if (is_array($value))
		{
			return $verifier->getMultiCount($table, $column, $value, $extra);
		}
		return $verifier->getCount($table, $column, $value, null, null, $extra);
	}
	protected function getExtraExistConditions(array $parameters)
	{
		return $this->getExtraConditions(array_values(array_slice($parameters, 2)));
	}
	protected function getExtraConditions(array $segments)
	{
		$extra = array();
		$count = count($segments);
		for ($i = 0; $i < $count; $i = $i + 2)
		{
			$extra[$segments[$i]] = $segments[$i + 1];
		}
		return $extra;
	}
	protected function validateIp($attribute, $value)
	{
		return filter_var($value, FILTER_VALIDATE_IP) !== false;
	}
	protected function validateEmail($attribute, $value)
	{
		return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
	}
	protected function validateUrl($attribute, $value)
	{
		return filter_var($value, FILTER_VALIDATE_URL) !== false;
	}
	protected function validateActiveUrl($attribute, $value)
	{
		$url = str_replace(array('http:
		return checkdnsrr($url, 'A');
	}
	protected function validateImage($attribute, $value)
	{
		return $this->validateMimes($attribute, $value, array('jpeg', 'png', 'gif', 'bmp', 'svg'));
	}
	protected function validateMimes($attribute, $value, $parameters)
	{
		if ( ! $this->isAValidFileInstance($value))
		{
			return false;
		}
		return $value->getPath() != '' && in_array($value->guessExtension(), $parameters);
	}
	protected function isAValidFileInstance($value)
	{
		if ($value instanceof UploadedFile && ! $value->isValid()) return false;
		return $value instanceof File;
	}
	protected function validateAlpha($attribute, $value)
	{
		return preg_match('/^[\pL\pM]+$/u', $value);
	}
	protected function validateAlphaNum($attribute, $value)
	{
		return preg_match('/^[\pL\pM\pN]+$/u', $value);
	}
	protected function validateAlphaDash($attribute, $value)
	{
		return preg_match('/^[\pL\pM\pN_-]+$/u', $value);
	}
	protected function validateRegex($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'regex');
		return preg_match($parameters[0], $value);
	}
	protected function validateDate($attribute, $value)
	{
		if ($value instanceof DateTime) return true;
		if (strtotime($value) === false) return false;
		$date = date_parse($value);
		return checkdate($date['month'], $date['day'], $date['year']);
	}
	protected function validateDateFormat($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'date_format');
		$parsed = date_parse_from_format($parameters[0], $value);
		return $parsed['error_count'] === 0 && $parsed['warning_count'] === 0;
	}
	protected function validateBefore($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'before');
		if ($format = $this->getDateFormat($attribute))
		{
			return $this->validateBeforeWithFormat($format, $value, $parameters);
		}
		if ( ! ($date = strtotime($parameters[0])))
		{
			return strtotime($value) < strtotime($this->getValue($parameters[0]));
		}
		return strtotime($value) < $date;
	}
	protected function validateBeforeWithFormat($format, $value, $parameters)
	{
		$param = $this->getValue($parameters[0]) ?: $parameters[0];
		return $this->checkDateTimeOrder($format, $value, $param);
	}
	protected function validateAfter($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'after');
		if ($format = $this->getDateFormat($attribute))
		{
			return $this->validateAfterWithFormat($format, $value, $parameters);
		}
		if ( ! ($date = strtotime($parameters[0])))
		{
			return strtotime($value) > strtotime($this->getValue($parameters[0]));
		}
		return strtotime($value) > $date;
	}
	protected function validateAfterWithFormat($format, $value, $parameters)
	{
		$param = $this->getValue($parameters[0]) ?: $parameters[0];
		return $this->checkDateTimeOrder($format, $param, $value);
	}
	protected function checkDateTimeOrder($format, $before, $after)
	{
		$before = $this->getDateTimeWithOptionalFormat($format, $before);
		$after = $this->getDateTimeWithOptionalFormat($format, $after);
		return ($before && $after) && ($after > $before);
	}
	protected function getDateTimeWithOptionalFormat($format, $value)
	{
		$date = DateTime::createFromFormat($format, $value);
		if ($date) return $date;
		try
		{
			return new DateTime($value);
		}
		catch (Exception $e)
		{
			return;
		}
	}
	protected function validateTimezone($attribute, $value)
	{
		try
		{
			new DateTimeZone($value);
		}
		catch (Exception $e)
		{
			return false;
		}
		return true;
	}
	protected function getDateFormat($attribute)
	{
		if ($result = $this->getRule($attribute, 'DateFormat'))
		{
			return $result[1][0];
		}
	}
	protected function getMessage($attribute, $rule)
	{
		$lowerRule = snake_case($rule);
		$inlineMessage = $this->getInlineMessage($attribute, $lowerRule);
		if ( ! is_null($inlineMessage))
		{
			return $inlineMessage;
		}
		$customKey = "validation.custom.{$attribute}.{$lowerRule}";
		$customMessage = $this->translator->trans($customKey);
		if ($customMessage !== $customKey)
		{
			return $customMessage;
		}
		elseif (in_array($rule, $this->sizeRules))
		{
			return $this->getSizeMessage($attribute, $rule);
		}
		$key = "validation.{$lowerRule}";
		if ($key != ($value = $this->translator->trans($key)))
		{
			return $value;
		}
		return $this->getInlineMessage(
			$attribute, $lowerRule, $this->fallbackMessages
		) ?: $key;
	}
	protected function getInlineMessage($attribute, $lowerRule, $source = null)
	{
		$source = $source ?: $this->customMessages;
		$keys = array("{$attribute}.{$lowerRule}", $lowerRule);
		foreach ($keys as $key)
		{
			if (isset($source[$key])) return $source[$key];
		}
	}
	protected function getSizeMessage($attribute, $rule)
	{
		$lowerRule = snake_case($rule);
		$type = $this->getAttributeType($attribute);
		$key = "validation.{$lowerRule}.{$type}";
		return $this->translator->trans($key);
	}
	protected function getAttributeType($attribute)
	{
		if ($this->hasRule($attribute, $this->numericRules))
		{
			return 'numeric';
		}
		elseif ($this->hasRule($attribute, array('Array')))
		{
			return 'array';
		}
		elseif (array_key_exists($attribute, $this->files))
		{
			return 'file';
		}
		return 'string';
	}
	protected function doReplacements($message, $attribute, $rule, $parameters)
	{
		$message = str_replace(':attribute', $this->getAttribute($attribute), $message);
		if (isset($this->replacers[snake_case($rule)]))
		{
			$message = $this->callReplacer($message, $attribute, snake_case($rule), $parameters);
		}
		elseif (method_exists($this, $replacer = "replace{$rule}"))
		{
			$message = $this->$replacer($message, $attribute, $rule, $parameters);
		}
		return $message;
	}
	protected function getAttributeList(array $values)
	{
		$attributes = array();
		foreach ($values as $key => $value)
		{
			$attributes[$key] = $this->getAttribute($value);
		}
		return $attributes;
	}
	protected function getAttribute($attribute)
	{
		if (isset($this->customAttributes[$attribute]))
		{
			return $this->customAttributes[$attribute];
		}
		$key = "validation.attributes.{$attribute}";
		if (($line = $this->translator->trans($key)) !== $key)
		{
			return $line;
		}
		return str_replace('_', ' ', snake_case($attribute));
	}
	public function getDisplayableValue($attribute, $value)
	{
		if (isset($this->customValues[$attribute][$value]))
		{
			return $this->customValues[$attribute][$value];
		}
		$key = "validation.values.{$attribute}.{$value}";
		if (($line = $this->translator->trans($key)) !== $key)
		{
			return $line;
		}
		return $value;
	}
	protected function replaceBetween($message, $attribute, $rule, $parameters)
	{
		return str_replace(array(':min', ':max'), $parameters, $message);
	}
	protected function replaceDigits($message, $attribute, $rule, $parameters)
	{
		return str_replace(':digits', $parameters[0], $message);
	}
	protected function replaceDigitsBetween($message, $attribute, $rule, $parameters)
	{
		return $this->replaceBetween($message, $attribute, $rule, $parameters);
	}
	protected function replaceSize($message, $attribute, $rule, $parameters)
	{
		return str_replace(':size', $parameters[0], $message);
	}
	protected function replaceMin($message, $attribute, $rule, $parameters)
	{
		return str_replace(':min', $parameters[0], $message);
	}
	protected function replaceMax($message, $attribute, $rule, $parameters)
	{
		return str_replace(':max', $parameters[0], $message);
	}
	protected function replaceIn($message, $attribute, $rule, $parameters)
	{
		foreach ($parameters as &$parameter)
		{
			$parameter = $this->getDisplayableValue($attribute, $parameter);
		}
		return str_replace(':values', implode(', ', $parameters), $message);
	}
	protected function replaceNotIn($message, $attribute, $rule, $parameters)
	{
		return $this->replaceIn($message, $attribute, $rule, $parameters);
	}
	protected function replaceMimes($message, $attribute, $rule, $parameters)
	{
		return str_replace(':values', implode(', ', $parameters), $message);
	}
	protected function replaceRequiredWith($message, $attribute, $rule, $parameters)
	{
		$parameters = $this->getAttributeList($parameters);
		return str_replace(':values', implode(' / ', $parameters), $message);
	}
	protected function replaceRequiredWithAll($message, $attribute, $rule, $parameters)
	{
		return $this->replaceRequiredWith($message, $attribute, $rule, $parameters);
	}
	protected function replaceRequiredWithout($message, $attribute, $rule, $parameters)
	{
		return $this->replaceRequiredWith($message, $attribute, $rule, $parameters);
	}
	protected function replaceRequiredWithoutAll($message, $attribute, $rule, $parameters)
	{
		return $this->replaceRequiredWith($message, $attribute, $rule, $parameters);
	}
	protected function replaceRequiredIf($message, $attribute, $rule, $parameters)
	{
		$parameters[1] = $this->getDisplayableValue($parameters[0], array_get($this->data, $parameters[0]));
		$parameters[0] = $this->getAttribute($parameters[0]);
		return str_replace(array(':other', ':value'), $parameters, $message);
	}
	protected function replaceSame($message, $attribute, $rule, $parameters)
	{
		return str_replace(':other', $this->getAttribute($parameters[0]), $message);
	}
	protected function replaceDifferent($message, $attribute, $rule, $parameters)
	{
		return $this->replaceSame($message, $attribute, $rule, $parameters);
	}
	protected function replaceDateFormat($message, $attribute, $rule, $parameters)
	{
		return str_replace(':format', $parameters[0], $message);
	}
	protected function replaceBefore($message, $attribute, $rule, $parameters)
	{
		if ( ! (strtotime($parameters[0])))
		{
			return str_replace(':date', $this->getAttribute($parameters[0]), $message);
		}
		return str_replace(':date', $parameters[0], $message);
	}
	protected function replaceAfter($message, $attribute, $rule, $parameters)
	{
		return $this->replaceBefore($message, $attribute, $rule, $parameters);
	}
	protected function hasRule($attribute, $rules)
	{
		return ! is_null($this->getRule($attribute, $rules));
	}
	protected function getRule($attribute, $rules)
	{
		if ( ! array_key_exists($attribute, $this->rules))
		{
			return;
		}
		$rules = (array) $rules;
		foreach ($this->rules[$attribute] as $rule)
		{
			list($rule, $parameters) = $this->parseRule($rule);
			if (in_array($rule, $rules)) return [$rule, $parameters];
		}
	}
	protected function parseRule($rules)
	{
		if (is_array($rules))
		{
			return $this->parseArrayRule($rules);
		}
		return $this->parseStringRule($rules);
	}
	protected function parseArrayRule(array $rules)
	{
		return array(studly_case(trim(array_get($rules, 0))), array_slice($rules, 1));
	}
	protected function parseStringRule($rules)
	{
		$parameters = [];
		if (strpos($rules, ':') !== false)
		{
			list($rules, $parameter) = explode(':', $rules, 2);
			$parameters = $this->parseParameters($rules, $parameter);
		}
		return array(studly_case(trim($rules)), $parameters);
	}
	protected function parseParameters($rule, $parameter)
	{
		if (strtolower($rule) == 'regex') return array($parameter);
		return str_getcsv($parameter);
	}
	public function getExtensions()
	{
		return $this->extensions;
	}
	public function addExtensions(array $extensions)
	{
		if ($extensions)
		{
			$keys = array_map('snake_case', array_keys($extensions));
			$extensions = array_combine($keys, array_values($extensions));
		}
		$this->extensions = array_merge($this->extensions, $extensions);
	}
	public function addImplicitExtensions(array $extensions)
	{
		$this->addExtensions($extensions);
		foreach ($extensions as $rule => $extension)
		{
			$this->implicitRules[] = studly_case($rule);
		}
	}
	public function addExtension($rule, $extension)
	{
		$this->extensions[snake_case($rule)] = $extension;
	}
	public function addImplicitExtension($rule, $extension)
	{
		$this->addExtension($rule, $extension);
		$this->implicitRules[] = studly_case($rule);
	}
	public function getReplacers()
	{
		return $this->replacers;
	}
	public function addReplacers(array $replacers)
	{
		if ($replacers)
		{
			$keys = array_map('snake_case', array_keys($replacers));
			$replacers = array_combine($keys, array_values($replacers));
		}
		$this->replacers = array_merge($this->replacers, $replacers);
	}
	public function addReplacer($rule, $replacer)
	{
		$this->replacers[snake_case($rule)] = $replacer;
	}
	public function getData()
	{
		return $this->data;
	}
	public function setData(array $data)
	{
		$this->data = $this->parseData($data);
	}
	public function getRules()
	{
		return $this->rules;
	}
	public function setRules(array $rules)
	{
		$this->rules = $this->explodeRules($rules);
		return $this;
	}
	public function setAttributeNames(array $attributes)
	{
		$this->customAttributes = $attributes;
		return $this;
	}
	public function setValueNames(array $values)
	{
		$this->customValues = $values;
		return $this;
	}
	public function getFiles()
	{
		return $this->files;
	}
	public function setFiles(array $files)
	{
		$this->files = $files;
		return $this;
	}
	public function getPresenceVerifier()
	{
		if ( ! isset($this->presenceVerifier))
		{
			throw new RuntimeException("Presence verifier has not been set.");
		}
		return $this->presenceVerifier;
	}
	public function setPresenceVerifier(PresenceVerifierInterface $presenceVerifier)
	{
		$this->presenceVerifier = $presenceVerifier;
	}
	public function getTranslator()
	{
		return $this->translator;
	}
	public function setTranslator(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}
	public function getCustomMessages()
	{
		return $this->customMessages;
	}
	public function setCustomMessages(array $messages)
	{
		$this->customMessages = array_merge($this->customMessages, $messages);
	}
	public function getCustomAttributes()
	{
		return $this->customAttributes;
	}
	public function addCustomAttributes(array $customAttributes)
	{
		$this->customAttributes = array_merge($this->customAttributes, $customAttributes);
		return $this;
	}
	public function getCustomValues()
	{
		return $this->customValues;
	}
	public function addCustomValues(array $customValues)
	{
		$this->customValues = array_merge($this->customValues, $customValues);
		return $this;
	}
	public function getFallbackMessages()
	{
		return $this->fallbackMessages;
	}
	public function setFallbackMessages(array $messages)
	{
		$this->fallbackMessages = $messages;
	}
	public function failed()
	{
		return $this->failedRules;
	}
	public function messages()
	{
		if ( ! $this->messages) $this->passes();
		return $this->messages;
	}
	public function errors()
	{
		return $this->messages();
	}
	public function getMessageBag()
	{
		return $this->messages();
	}
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}
	protected function callExtension($rule, $parameters)
	{
		$callback = $this->extensions[$rule];
		if ($callback instanceof Closure)
		{
			return call_user_func_array($callback, $parameters);
		}
		elseif (is_string($callback))
		{
			return $this->callClassBasedExtension($callback, $parameters);
		}
	}
	protected function callClassBasedExtension($callback, $parameters)
	{
		list($class, $method) = explode('@', $callback);
		return call_user_func_array(array($this->container->make($class), $method), $parameters);
	}
	protected function callReplacer($message, $attribute, $rule, $parameters)
	{
		$callback = $this->replacers[$rule];
		if ($callback instanceof Closure)
		{
			return call_user_func_array($callback, func_get_args());
		}
		elseif (is_string($callback))
		{
			return $this->callClassBasedReplacer($callback, $message, $attribute, $rule, $parameters);
		}
	}
	protected function callClassBasedReplacer($callback, $message, $attribute, $rule, $parameters)
	{
		list($class, $method) = explode('@', $callback);
		return call_user_func_array(array($this->container->make($class), $method), array_slice(func_get_args(), 1));
	}
	protected function requireParameterCount($count, $parameters, $rule)
	{
		if (count($parameters) < $count)
		{
			throw new InvalidArgumentException("Validation rule $rule requires at least $count parameters.");
		}
	}
	public function __call($method, $parameters)
	{
		$rule = snake_case(substr($method, 8));
		if (isset($this->extensions[$rule]))
		{
			return $this->callExtension($rule, $parameters);
		}
		throw new BadMethodCallException("Method [$method] does not exist.");
	}
}
