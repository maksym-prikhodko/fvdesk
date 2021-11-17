<?php
namespace Symfony\Component\HttpKernel\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
interface DataCollectorInterface
{
    public function collect(Request $request, Response $response, \Exception $exception = null);
    public function getName();
}
