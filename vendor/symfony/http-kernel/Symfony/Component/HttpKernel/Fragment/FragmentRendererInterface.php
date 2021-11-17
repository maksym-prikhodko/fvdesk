<?php
namespace Symfony\Component\HttpKernel\Fragment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpFoundation\Response;
interface FragmentRendererInterface
{
    public function render($uri, Request $request, array $options = array());
    public function getName();
}
