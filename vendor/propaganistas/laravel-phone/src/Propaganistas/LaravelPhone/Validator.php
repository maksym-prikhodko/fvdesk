<?php namespace Propaganistas\LaravelPhone;
class Validator
{
	public function phone($attribute, $value, $parameters, $validator)
	{
		$data = $validator->getData();
		if (!empty($parameters)) {
			$countries = $parameters;
		}
		elseif (isset($data[$attribute.'_country'])) {
			$countries = array($data[$attribute.'_country']);
		}
		else {
			return FALSE;
		}
		foreach ($countries as $key => $country) {
			if (!$this->phone_country($country)) {
				unset($countries[$key]);
			}
		}
		foreach ($countries as $country) {
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$phoneProto = $phoneUtil->parse($value, $country);
				if ($phoneUtil->isValidNumber($phoneProto)) {
					return TRUE;
				}
			}
			catch (\libphonenumber\NumberParseException $e) {}
		}
		return FALSE;
	}
	public function phone_country($country)
	{
		return (strlen($country) === 2 && ctype_alpha($country) && ctype_upper($country) && $country != 'ZZ');
	}
}
