<?php
namespace Symfony\Component\Translation;
interface TranslatorBagInterface
{
    public function getCatalogue($locale = null);
}
