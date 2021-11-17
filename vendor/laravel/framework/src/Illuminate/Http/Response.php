<?php namespace Illuminate\Http;
use ArrayObject;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Renderable;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
class Response extends BaseResponse {
	use ResponseTrait;
	public $original;
	public function setContent($content)
	{
		$this->original = $content;
		if ($this->shouldBeJson($content))
		{
			$this->header('Content-Type', 'application/json');
			$content = $this->morphToJson($content);
		}
		elseif ($content instanceof Renderable)
		{
			$content = $content->render();
		}
		return parent::setContent($content);
	}
	protected function morphToJson($content)
	{
		if ($content instanceof Jsonable) return $content->toJson();
		return json_encode($content);
	}
	protected function shouldBeJson($content)
	{
		return $content instanceof Jsonable ||
			   $content instanceof ArrayObject ||
			   is_array($content);
	}
	public function getOriginalContent()
	{
		return $this->original;
	}
}
