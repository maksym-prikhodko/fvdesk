<?php
namespace Psy\Presenter;
use Psy\Util\Json;
use Symfony\Component\Console\Formatter\OutputFormatter;
class ScalarPresenter implements Presenter
{
    public function canPresent($value)
    {
        return is_scalar($value) || is_null($value);
    }
    public function presentRef($value)
    {
        return $this->present($value);
    }
    public function present($value, $depth = null, $options = 0)
    {
        $formatted = $this->format($value);
        if ($typeStyle = $this->getTypeStyle($value)) {
            return sprintf('<%s>%s</%s>', $typeStyle, $formatted, $typeStyle);
        } else {
            return $formatted;
        }
    }
    private function format($value)
    {
        if (is_float($value)) {
            if (is_nan($value)) {
                return 'NAN';
            } elseif (is_infinite($value)) {
                return $value === INF ? 'INF' : '-INF';
            }
            $float = Json::encode($value);
            if (strpos($float, '.') === false) {
                $float .= '.0';
            }
            return $float;
        }
        return OutputFormatter::escape(Json::encode($value));
    }
    private function getTypeStyle($value)
    {
        if (is_int($value) || is_float($value)) {
            return 'number';
        } elseif (is_string($value)) {
            return 'string';
        } elseif (is_bool($value) || is_null($value)) {
            return 'bool';
        }
    }
}
