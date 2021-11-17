<?php
namespace Psy\Presenter;
class ClosurePresenter implements Presenter, PresenterManagerAware
{
    const FMT     = '<keyword>function</keyword> (%s)%s { <comment>...</comment> }';
    const USE_FMT = ' use (%s)';
    protected $manager;
    public function setPresenterManager(PresenterManager $manager)
    {
        $this->manager = $manager;
    }
    public function canPresent($value)
    {
        return $value instanceof \Closure;
    }
    public function presentRef($value)
    {
        return sprintf(
            self::FMT,
            $this->formatParams($value),
            $this->formatStaticVariables($value)
        );
    }
    public function present($value, $depth = null, $options = 0)
    {
        return $this->presentRef($value);
    }
    protected function formatParams(\Closure $value)
    {
        $r = new \ReflectionFunction($value);
        $params = array_map(array($this, 'formatParam'), $r->getParameters());
        return implode(', ', $params);
    }
    protected function formatParam(\ReflectionParameter $param)
    {
        $ret = $this->formatParamName($param->name);
        if ($param->isOptional()) {
            $ret .= ' = ';
            if (self::isParamDefaultValueConstant($param)) {
                $name = $param->getDefaultValueConstantName();
                $ret .= '<const>' . $name . '</const>';
            } elseif ($param->isDefaultValueAvailable()) {
                $ret .= $this->manager->presentRef($param->getDefaultValue());
            } else {
                $ret .= '<urgent>?</urgent>';
            }
        }
        return $ret;
    }
    protected function formatStaticVariables(\Closure $value)
    {
        $r = new \ReflectionFunction($value);
        $used = $r->getStaticVariables();
        if (empty($used)) {
            return '';
        }
        $names = array_map(array($this, 'formatParamName'), array_keys($used));
        return sprintf(
            self::USE_FMT,
            implode(', ', $names)
        );
    }
    protected function formatParamName($name)
    {
        return sprintf('$<strong>%s</strong>', $name);
    }
    protected static function isParamDefaultValueConstant(\ReflectionParameter $param)
    {
        if (defined('HHVM_VERSION')) {
            return false;
        }
        return version_compare(PHP_VERSION, '5.4.3', '>=') && $param->isDefaultValueConstant();
    }
}
