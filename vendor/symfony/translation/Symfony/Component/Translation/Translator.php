<?php
namespace Symfony\Component\Translation;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Config\ConfigCache;
class Translator implements TranslatorInterface, TranslatorBagInterface
{
    protected $catalogues = array();
    protected $locale;
    private $fallbackLocales = array();
    private $loaders = array();
    private $resources = array();
    private $selector;
    private $cacheDir;
    private $debug;
    public function __construct($locale, MessageSelector $selector = null, $cacheDir = null, $debug = false)
    {
        $this->setLocale($locale);
        $this->selector = $selector ?: new MessageSelector();
        $this->cacheDir = $cacheDir;
        $this->debug = $debug;
    }
    public function addLoader($format, LoaderInterface $loader)
    {
        $this->loaders[$format] = $loader;
    }
    public function addResource($format, $resource, $locale, $domain = null)
    {
        if (null === $domain) {
            $domain = 'messages';
        }
        $this->assertValidLocale($locale);
        $this->resources[$locale][] = array($format, $resource, $domain);
        if (in_array($locale, $this->fallbackLocales)) {
            $this->catalogues = array();
        } else {
            unset($this->catalogues[$locale]);
        }
    }
    public function setLocale($locale)
    {
        $this->assertValidLocale($locale);
        $this->locale = $locale;
    }
    public function getLocale()
    {
        return $this->locale;
    }
    public function setFallbackLocale($locales)
    {
        $this->setFallbackLocales(is_array($locales) ? $locales : array($locales));
    }
    public function setFallbackLocales(array $locales)
    {
        $this->catalogues = array();
        foreach ($locales as $locale) {
            $this->assertValidLocale($locale);
        }
        $this->fallbackLocales = $locales;
    }
    public function getFallbackLocales()
    {
        return $this->fallbackLocales;
    }
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        } else {
            $this->assertValidLocale($locale);
        }
        if (null === $domain) {
            $domain = 'messages';
        }
        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }
        return strtr($this->catalogues[$locale]->get((string) $id, $domain), $parameters);
    }
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        } else {
            $this->assertValidLocale($locale);
        }
        if (null === $domain) {
            $domain = 'messages';
        }
        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }
        $id = (string) $id;
        $catalogue = $this->catalogues[$locale];
        while (!$catalogue->defines($id, $domain)) {
            if ($cat = $catalogue->getFallbackCatalogue()) {
                $catalogue = $cat;
                $locale = $catalogue->getLocale();
            } else {
                break;
            }
        }
        return strtr($this->selector->choose($catalogue->get($id, $domain), (int) $number, $locale), $parameters);
    }
    public function getCatalogue($locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }
        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }
        return $this->catalogues[$locale];
    }
    protected function getLoaders()
    {
        return $this->loaders;
    }
    public function getMessages($locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }
        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }
        $catalogues = array();
        $catalogues[] = $catalogue = $this->catalogues[$locale];
        while ($catalogue = $catalogue->getFallbackCatalogue()) {
            $catalogues[] = $catalogue;
        }
        $messages = array();
        for ($i = count($catalogues) - 1; $i >= 0; $i--) {
            $localeMessages = $catalogues[$i]->all();
            $messages = array_replace_recursive($messages, $localeMessages);
        }
        return $messages;
    }
    protected function loadCatalogue($locale)
    {
        if (null === $this->cacheDir) {
            $this->initializeCatalogue($locale);
        } else {
            $this->initializeCacheCatalogue($locale);
        }
    }
    protected function initializeCatalogue($locale)
    {
        $this->assertValidLocale($locale);
        try {
            $this->doLoadCatalogue($locale);
        } catch (NotFoundResourceException $e) {
            if (!$this->computeFallbackLocales($locale)) {
                throw $e;
            }
        }
        $this->loadFallbackCatalogues($locale);
    }
    private function initializeCacheCatalogue($locale)
    {
        if (isset($this->catalogues[$locale])) {
            return;
        }
        $this->assertValidLocale($locale);
        $cache = new ConfigCache($this->cacheDir.'/catalogue.'.$locale.'.php', $this->debug);
        if (!$cache->isFresh()) {
            $this->initializeCatalogue($locale);
            $fallbackContent = '';
            $current = '';
            $replacementPattern = '/[^a-z0-9_]/i';
            foreach ($this->computeFallbackLocales($locale) as $fallback) {
                $fallbackSuffix = ucfirst(preg_replace($replacementPattern, '_', $fallback));
                $currentSuffix = ucfirst(preg_replace($replacementPattern, '_', $current));
                $fallbackContent .= sprintf(<<<EOF
\$catalogue%s = new MessageCatalogue('%s', %s);
\$catalogue%s->addFallbackCatalogue(\$catalogue%s);
EOF
                    ,
                    $fallbackSuffix,
                    $fallback,
                    var_export($this->catalogues[$fallback]->all(), true),
                    $currentSuffix,
                    $fallbackSuffix
                );
                $current = $fallback;
            }
            $content = sprintf(<<<EOF
<?php
use Symfony\Component\Translation\MessageCatalogue;
\$catalogue = new MessageCatalogue('%s', %s);
%s
return \$catalogue;
EOF
                ,
                $locale,
                var_export($this->catalogues[$locale]->all(), true),
                $fallbackContent
            );
            $cache->write($content, $this->catalogues[$locale]->getResources());
            return;
        }
        $this->catalogues[$locale] = include $cache;
    }
    private function doLoadCatalogue($locale)
    {
        $this->catalogues[$locale] = new MessageCatalogue($locale);
        if (isset($this->resources[$locale])) {
            foreach ($this->resources[$locale] as $resource) {
                if (!isset($this->loaders[$resource[0]])) {
                    throw new \RuntimeException(sprintf('The "%s" translation loader is not registered.', $resource[0]));
                }
                $this->catalogues[$locale]->addCatalogue($this->loaders[$resource[0]]->load($resource[1], $locale, $resource[2]));
            }
        }
    }
    private function loadFallbackCatalogues($locale)
    {
        $current = $this->catalogues[$locale];
        foreach ($this->computeFallbackLocales($locale) as $fallback) {
            if (!isset($this->catalogues[$fallback])) {
                $this->doLoadCatalogue($fallback);
            }
            $current->addFallbackCatalogue($this->catalogues[$fallback]);
            $current = $this->catalogues[$fallback];
        }
    }
    protected function computeFallbackLocales($locale)
    {
        $locales = array();
        foreach ($this->fallbackLocales as $fallback) {
            if ($fallback === $locale) {
                continue;
            }
            $locales[] = $fallback;
        }
        if (strrchr($locale, '_') !== false) {
            array_unshift($locales, substr($locale, 0, -strlen(strrchr($locale, '_'))));
        }
        return array_unique($locales);
    }
    protected function assertValidLocale($locale)
    {
        if (1 !== preg_match('/^[a-z0-9@_\\.\\-]*$/i', $locale)) {
            throw new \InvalidArgumentException(sprintf('Invalid "%s" locale.', $locale));
        }
    }
}
