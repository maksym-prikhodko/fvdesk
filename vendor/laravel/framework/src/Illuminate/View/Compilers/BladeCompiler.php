<?php namespace Illuminate\View\Compilers;
class BladeCompiler extends Compiler implements CompilerInterface {
	protected $extensions = array();
	protected $path;
	protected $compilers = array(
		'Extensions',
		'Statements',
		'Comments',
		'Echos',
	);
	protected $rawTags = array('{!!', '!!}');
	protected $contentTags = array('{{', '}}');
	protected $escapedTags = array('{{{', '}}}');
	protected $echoFormat = 'e(%s)';
	protected $footer = array();
	protected $forelseCounter = 0;
	public function compile($path = null)
	{
		$this->footer = array();
		if ($path)
		{
			$this->setPath($path);
		}
		$contents = $this->compileString($this->files->get($path));
		if ( ! is_null($this->cachePath))
		{
			$this->files->put($this->getCompiledPath($this->getPath()), $contents);
		}
	}
	public function getPath()
	{
		return $this->path;
	}
	public function setPath($path)
	{
		$this->path = $path;
	}
	public function compileString($value)
	{
		$result = '';
		foreach (token_get_all($value) as $token)
		{
			$result .= is_array($token) ? $this->parseToken($token) : $token;
		}
		if (count($this->footer) > 0)
		{
			$result = ltrim($result, PHP_EOL)
					.PHP_EOL.implode(PHP_EOL, array_reverse($this->footer));
		}
		return $result;
	}
	protected function parseToken($token)
	{
		list($id, $content) = $token;
		if ($id == T_INLINE_HTML)
		{
			foreach ($this->compilers as $type)
			{
				$content = $this->{"compile{$type}"}($content);
			}
		}
		return $content;
	}
	protected function compileExtensions($value)
	{
		foreach ($this->extensions as $compiler)
		{
			$value = call_user_func($compiler, $value, $this);
		}
		return $value;
	}
	protected function compileComments($value)
	{
		$pattern = sprintf('/%s--((.|\s)*?)--%s/', $this->contentTags[0], $this->contentTags[1]);
		return preg_replace($pattern, '<?php  ?>', $value);
	}
	protected function compileEchos($value)
	{
		foreach ($this->getEchoMethods() as $method => $length)
		{
			$value = $this->$method($value);
		}
		return $value;
	}
	protected function getEchoMethods()
	{
		$methods = [
			"compileRawEchos" => strlen(stripcslashes($this->rawTags[0])),
			"compileEscapedEchos" => strlen(stripcslashes($this->escapedTags[0])),
			"compileRegularEchos" => strlen(stripcslashes($this->contentTags[0])),
		];
		uksort($methods, function($method1, $method2) use ($methods)
		{
			if ($methods[$method1] > $methods[$method2]) return -1;
			if ($methods[$method1] < $methods[$method2]) return 1;
			if ($method1 === "compileRawEchos") return -1;
			if ($method2 === "compileRawEchos") return 1;
			if ($method1 === "compileEscapedEchos") return -1;
			if ($method2 === "compileEscapedEchos") return 1;
		});
		return $methods;
	}
	protected function compileStatements($value)
	{
		$callback = function($match)
		{
			if (method_exists($this, $method = 'compile'.ucfirst($match[1])))
			{
				$match[0] = $this->$method(array_get($match, 3));
			}
			return isset($match[3]) ? $match[0] : $match[0].$match[2];
		};
		return preg_replace_callback('/\B@(\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', $callback, $value);
	}
	protected function compileRawEchos($value)
	{
		$pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->rawTags[0], $this->rawTags[1]);
		$callback = function($matches)
		{
			$whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
			return $matches[1] ? substr($matches[0], 1) : '<?php echo '.$this->compileEchoDefaults($matches[2]).'; ?>'.$whitespace;
		};
		return preg_replace_callback($pattern, $callback, $value);
	}
	protected function compileRegularEchos($value)
	{
		$pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);
		$callback = function($matches)
		{
			$whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
			$wrapped = sprintf($this->echoFormat, $this->compileEchoDefaults($matches[2]));
			return $matches[1] ? substr($matches[0], 1) : '<?php echo '.$wrapped.'; ?>'.$whitespace;
		};
		return preg_replace_callback($pattern, $callback, $value);
	}
	protected function compileEscapedEchos($value)
	{
		$pattern = sprintf('/%s\s*(.+?)\s*%s(\r?\n)?/s', $this->escapedTags[0], $this->escapedTags[1]);
		$callback = function($matches)
		{
			$whitespace = empty($matches[2]) ? '' : $matches[2].$matches[2];
			return '<?php echo e('.$this->compileEchoDefaults($matches[1]).'); ?>'.$whitespace;
		};
		return preg_replace_callback($pattern, $callback, $value);
	}
	public function compileEchoDefaults($value)
	{
		return preg_replace('/^(?=\$)(.+?)(?:\s+or\s+)(.+?)$/s', 'isset($1) ? $1 : $2', $value);
	}
	protected function compileEach($expression)
	{
		return "<?php echo \$__env->renderEach{$expression}; ?>";
	}
	protected function compileYield($expression)
	{
		return "<?php echo \$__env->yieldContent{$expression}; ?>";
	}
	protected function compileShow($expression)
	{
		return "<?php echo \$__env->yieldSection(); ?>";
	}
	protected function compileSection($expression)
	{
		return "<?php \$__env->startSection{$expression}; ?>";
	}
	protected function compileAppend($expression)
	{
		return "<?php \$__env->appendSection(); ?>";
	}
	protected function compileEndsection($expression)
	{
		return "<?php \$__env->stopSection(); ?>";
	}
	protected function compileStop($expression)
	{
		return "<?php \$__env->stopSection(); ?>";
	}
	protected function compileOverwrite($expression)
	{
		return "<?php \$__env->stopSection(true); ?>";
	}
	protected function compileUnless($expression)
	{
		return "<?php if ( ! $expression): ?>";
	}
	protected function compileEndunless($expression)
	{
		return "<?php endif; ?>";
	}
	protected function compileLang($expression)
	{
		return "<?php echo \\Illuminate\\Support\\Facades\\Lang::get$expression; ?>";
	}
	protected function compileChoice($expression)
	{
		return "<?php echo \\Illuminate\\Support\\Facades\\Lang::choice$expression; ?>";
	}
	protected function compileElse($expression)
	{
		return "<?php else: ?>";
	}
	protected function compileFor($expression)
	{
		return "<?php for{$expression}: ?>";
	}
	protected function compileForeach($expression)
	{
		return "<?php foreach{$expression}: ?>";
	}
	protected function compileForelse($expression)
	{
		$empty = '$__empty_' . ++$this->forelseCounter;
		return "<?php {$empty} = true; foreach{$expression}: {$empty} = false; ?>";
	}
	protected function compileIf($expression)
	{
		return "<?php if{$expression}: ?>";
	}
	protected function compileElseif($expression)
	{
		return "<?php elseif{$expression}: ?>";
	}
	protected function compileEmpty($expression)
	{
		$empty = '$__empty_' . $this->forelseCounter--;
		return "<?php endforeach; if ({$empty}): ?>";
	}
	protected function compileWhile($expression)
	{
		return "<?php while{$expression}: ?>";
	}
	protected function compileEndwhile($expression)
	{
		return "<?php endwhile; ?>";
	}
	protected function compileEndfor($expression)
	{
		return "<?php endfor; ?>";
	}
	protected function compileEndforeach($expression)
	{
		return "<?php endforeach; ?>";
	}
	protected function compileEndif($expression)
	{
		return "<?php endif; ?>";
	}
	protected function compileEndforelse($expression)
	{
		return "<?php endif; ?>";
	}
	protected function compileExtends($expression)
	{
		if (starts_with($expression, '('))
		{
			$expression = substr($expression, 1, -1);
		}
		$data = "<?php echo \$__env->make($expression, array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>";
		$this->footer[] = $data;
		return '';
	}
	protected function compileInclude($expression)
	{
		if (starts_with($expression, '('))
		{
			$expression = substr($expression, 1, -1);
		}
		return "<?php echo \$__env->make($expression, array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>";
	}
	protected function compileStack($expression)
	{
		return "<?php echo \$__env->yieldContent{$expression}; ?>";
	}
	protected function compilePush($expression)
	{
		return "<?php \$__env->startSection{$expression}; ?>";
	}
	protected function compileEndpush($expression)
	{
		return "<?php \$__env->appendSection(); ?>";
	}
	public function extend(callable $compiler)
	{
		$this->extensions[] = $compiler;
	}
	public function createMatcher($function)
	{
		return '/(?<!\w)(\s*)@'.$function.'(\s*\(.*\))/';
	}
	public function createOpenMatcher($function)
	{
		return '/(?<!\w)(\s*)@'.$function.'(\s*\(.*)\)/';
	}
	public function createPlainMatcher($function)
	{
		return '/(?<!\w)(\s*)@'.$function.'(\s*)/';
	}
	public function setRawTags($openTag, $closeTag)
	{
		$this->rawTags = array(preg_quote($openTag), preg_quote($closeTag));
	}
	public function setContentTags($openTag, $closeTag, $escaped = false)
	{
		$property = ($escaped === true) ? 'escapedTags' : 'contentTags';
		$this->{$property} = array(preg_quote($openTag), preg_quote($closeTag));
	}
	public function setEscapedContentTags($openTag, $closeTag)
	{
		$this->setContentTags($openTag, $closeTag, true);
	}
	public function getContentTags()
	{
		return $this->getTags();
	}
	public function getEscapedContentTags()
	{
		return $this->getTags(true);
	}
	protected function getTags($escaped = false)
	{
		$tags = $escaped ? $this->escapedTags : $this->contentTags;
		return array_map('stripcslashes', $tags);
	}
	public function setEchoFormat($format)
	{
		$this->echoFormat = $format;
	}
}
