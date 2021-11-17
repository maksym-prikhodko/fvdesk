<?php
namespace Symfony\Component\Translation\Loader;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
interface LoaderInterface
{
    public function load($resource, $locale, $domain = 'messages');
}
