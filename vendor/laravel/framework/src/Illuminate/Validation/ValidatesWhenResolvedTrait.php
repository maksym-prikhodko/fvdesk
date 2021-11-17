<?php namespace Illuminate\Validation;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Contracts\Validation\UnauthorizedException;
trait ValidatesWhenResolvedTrait {
	public function validate()
	{
		$instance = $this->getValidatorInstance();
		if ( ! $this->passesAuthorization())
		{
			$this->failedAuthorization();
		}
		elseif ( ! $instance->passes())
		{
			$this->failedValidation($instance);
		}
	}
	protected function getValidatorInstance()
	{
		return $this->validator();
	}
	protected function failedValidation(Validator $validator)
	{
		throw new ValidationException($validator);
	}
	protected function passesAuthorization()
	{
		if (method_exists($this, 'authorize'))
		{
			return $this->authorize();
		}
		return true;
	}
	protected function failedAuthorization()
	{
		throw new UnauthorizedException;
	}
}
