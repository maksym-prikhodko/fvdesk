<?php
namespace Symfony\Component\Translation;
use Symfony\Component\Config\Resource\ResourceInterface;
interface MessageCatalogueInterface
{
    public function getLocale();
    public function getDomains();
    public function all($domain = null);
    public function set($id, $translation, $domain = 'messages');
    public function has($id, $domain = 'messages');
    public function defines($id, $domain = 'messages');
    public function get($id, $domain = 'messages');
    public function replace($messages, $domain = 'messages');
    public function add($messages, $domain = 'messages');
    public function addCatalogue(MessageCatalogueInterface $catalogue);
    public function addFallbackCatalogue(MessageCatalogueInterface $catalogue);
    public function getFallbackCatalogue();
    public function getResources();
    public function addResource(ResourceInterface $resource);
}
