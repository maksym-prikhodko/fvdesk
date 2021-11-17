<?php namespace Illuminate\Support\Debug;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;
class Dumper {
	public function dump($value)
	{
		$dumper = 'cli' === PHP_SAPI ? new CliDumper : new HtmlDumper;
		$dumper->dump((new VarCloner)->cloneVar($value));
	}
}
