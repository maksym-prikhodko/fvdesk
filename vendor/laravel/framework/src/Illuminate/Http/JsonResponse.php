<?php namespace Illuminate\Http;
use Illuminate\Contracts\Support\Jsonable;
use Symfony\Component\HttpFoundation\JsonResponse as BaseJsonResponse;
class JsonResponse extends BaseJsonResponse {
	use ResponseTrait;
	protected $jsonOptions;
	public function __construct($data = null, $status = 200, $headers = array(), $options = 0)
	{
		$this->jsonOptions = $options;
		parent::__construct($data, $status, $headers);
	}
	public function getData($assoc = false, $depth = 512)
	{
		return json_decode($this->data, $assoc, $depth);
	}
	public function setData($data = array())
	{
		$this->data = $data instanceof Jsonable
								   ? $data->toJson($this->jsonOptions)
								   : json_encode($data, $this->jsonOptions);
		return $this->update();
	}
	public function getJsonOptions()
	{
		return $this->jsonOptions;
	}
	public function setJsonOptions($options)
	{
		$this->jsonOptions = $options;
		return $this->setData($this->getData());
	}
}
