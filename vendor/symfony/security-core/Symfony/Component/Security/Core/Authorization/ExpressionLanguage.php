<?php
namespace Symfony\Component\Security\Core\Authorization;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface;
class ExpressionLanguage extends BaseExpressionLanguage
{
    public function __construct(ParserCacheInterface $cache = null, array $providers = array())
    {
        array_unshift($providers, new ExpressionLanguageProvider());
        parent::__construct($cache, $providers);
    }
}
