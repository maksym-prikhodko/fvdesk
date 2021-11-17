<?php
namespace Symfony\Component\Translation\Extractor;
use Symfony\Component\Translation\MessageCatalogue;
interface ExtractorInterface
{
    public function extract($directory, MessageCatalogue $catalogue);
    public function setPrefix($prefix);
}
