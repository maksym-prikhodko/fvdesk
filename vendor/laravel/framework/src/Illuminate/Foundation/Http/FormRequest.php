<?php namespace Illuminate\Foundation\Http;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Container\Container;
use Illuminate\Validation\Validator;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Validation\ValidatesWhenResolvedTrait;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
class FormRequest extends Request implements ValidatesWhenResolved {
	use ValidatesWhenResolvedTrait;
	protected $container;
	protected $redirector;
	protected $redirect;
	protected $redirectRoute;
	protected $redirectAction;
	protected $errorBag = 'default';
	protected $dontFlash = ['password', 'password_confirmation'];
	protected function getValidatorInstance()
	{
		$factory = $this->container->make('Illuminate\Validation\Factory');
		if (method_exists($this, 'validator'))
		{
			return $this->container->call([$this, 'validator'], compact('factory'));
		}
		return $factory->make(
			$this->all(), $this->container->call([$this, 'rules']), $this->messages(), $this->attributes()
		);
	}
	protected function failedValidation(Validator $validator)
	{
		throw new HttpResponseException($this->response(
			$this->formatErrors($validator)
		));
	}
	protected function passesAuthorization()
	{
		if (method_exists($this, 'authorize'))
		{
			return $this->container->call([$this, 'authorize']);
		}
		return false;
	}
	protected function failedAuthorization()
	{
		throw new HttpResponseException($this->forbiddenResponse());
	}
	public function response(array $errors)
	{
		if ($this->ajax() || $this->wantsJson())
		{
			return new JsonResponse($errors, 422);
		}
		return $this->redirector->to($this->getRedirectUrl())
                                        ->withInput($this->except($this->dontFlash))
                                        ->withErrors($errors, $this->errorBag);
	}
	public function forbiddenResponse()
	{
		return new Response('Forbidden', 403);
	}
	protected function formatErrors(Validator $validator)
	{
		return $validator->errors()->getMessages();
	}
	protected function getRedirectUrl()
	{
		$url = $this->redirector->getUrlGenerator();
		if ($this->redirect)
		{
			return $url->to($this->redirect);
		}
		elseif ($this->redirectRoute)
		{
			return $url->route($this->redirectRoute);
		}
		elseif ($this->redirectAction)
		{
			return $url->action($this->redirectAction);
		}
		return $url->previous();
	}
	public function setRedirector(Redirector $redirector)
	{
		$this->redirector = $redirector;
		return $this;
	}
	public function setContainer(Container $container)
	{
		$this->container = $container;
		return $this;
	}
	public function messages()
	{
		return [];
	}
	public function attributes()
	{
		return [];
	}
}
