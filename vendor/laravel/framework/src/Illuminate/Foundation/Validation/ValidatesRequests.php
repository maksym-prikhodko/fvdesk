<?php namespace Illuminate\Foundation\Validation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Validator;
use Illuminate\Http\Exception\HttpResponseException;
trait ValidatesRequests {
	public function validate(Request $request, array $rules, array $messages = array())
	{
		$validator = $this->getValidationFactory()->make($request->all(), $rules, $messages);
		if ($validator->fails())
		{
			$this->throwValidationException($request, $validator);
		}
	}
	protected function throwValidationException(Request $request, $validator)
	{
		throw new HttpResponseException($this->buildFailedValidationResponse(
			$request, $this->formatValidationErrors($validator)
		));
	}
	protected function buildFailedValidationResponse(Request $request, array $errors)
	{
		if ($request->ajax() || $request->wantsJson())
		{
			return new JsonResponse($errors, 422);
		}
		return redirect()->to($this->getRedirectUrl())
						->withInput($request->input())
						->withErrors($errors, $this->errorBag());
	}
	protected function formatValidationErrors(Validator $validator)
	{
		return $validator->errors()->getMessages();
	}
	protected function getRedirectUrl()
	{
		return app('Illuminate\Routing\UrlGenerator')->previous();
	}
	protected function getValidationFactory()
	{
		return app('Illuminate\Contracts\Validation\Factory');
	}
	protected function errorBag()
	{
		return 'default';
	}
}
