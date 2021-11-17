<?php
namespace Psy\Formatter;
use Psy\Reflection\ReflectionConstant;
use Psy\Util\Json;
use Symfony\Component\Console\Formatter\OutputFormatter;
class SignatureFormatter implements Formatter
{
    public static function format(\Reflector $reflector)
    {
        switch (true) {
            case $reflector instanceof \ReflectionFunction:
                return self::formatFunction($reflector);
            case $reflector instanceof \ReflectionClass:
                return self::formatClass($reflector);
            case $reflector instanceof ReflectionConstant:
                return self::formatConstant($reflector);
            case $reflector instanceof \ReflectionMethod:
                return self::formatMethod($reflector);
            case $reflector instanceof \ReflectionProperty:
                return self::formatProperty($reflector);
            default:
                throw new \InvalidArgumentException('Unexpected Reflector class: ' . get_class($reflector));
        }
    }
    public static function formatName(\Reflector $reflector)
    {
        return $reflector->getName();
    }
    private static function formatModifiers(\Reflector $reflector)
    {
        return implode(' ', array_map(function ($modifier) {
            return sprintf('<keyword>%s</keyword>', $modifier);
        }, \Reflection::getModifierNames($reflector->getModifiers())));
    }
    private static function formatClass(\ReflectionClass $reflector)
    {
        $chunks = array();
        if ($modifiers = self::formatModifiers($reflector)) {
            $chunks[] = $modifiers;
        }
        if (version_compare(PHP_VERSION, '5.4', '>=') && $reflector->isTrait()) {
            $chunks[] = 'trait';
        } else {
            $chunks[] = $reflector->isInterface() ? 'interface' : 'class';
        }
        $chunks[] = sprintf('<class>%s</class>', self::formatName($reflector));
        if ($parent = $reflector->getParentClass()) {
            $chunks[] = 'extends';
            $chunks[] = sprintf('<class>%s</class>', $parent->getName());
        }
        $interfaces = $reflector->getInterfaceNames();
        if (!empty($interfaces)) {
            $chunks[] = 'implements';
            $chunks[] = implode(', ', array_map(function ($name) {
                return sprintf('<class>%s</class>', $name);
            }, $interfaces));
        }
        return implode(' ', $chunks);
    }
    private static function formatConstant(ReflectionConstant $reflector)
    {
        $value = $reflector->getValue();
        $style = self::getTypeStyle($value);
        return sprintf(
            '<keyword>const</keyword> <const>%s</const> = <%s>%s</%s>',
            self::formatName($reflector),
            $style,
            OutputFormatter::escape(Json::encode($value)),
            $style
        );
    }
    private static function getTypeStyle($value)
    {
        if (is_int($value) || is_float($value)) {
            return 'number';
        } elseif (is_string($value)) {
            return 'string';
        } elseif (is_bool($value) || is_null($value)) {
            return 'bool';
        } else {
            return 'strong';
        }
    }
    private static function formatProperty(\ReflectionProperty $reflector)
    {
        return sprintf(
            '%s <strong>$%s</strong>',
            self::formatModifiers($reflector),
            $reflector->getName()
        );
    }
    private static function formatFunction(\ReflectionFunctionAbstract $reflector)
    {
        return sprintf(
            '<keyword>function</keyword> %s<function>%s</function>(%s)',
            $reflector->returnsReference() ? '&' : '',
            self::formatName($reflector),
            implode(', ', self::formatFunctionParams($reflector))
        );
    }
    private static function formatMethod(\ReflectionMethod $reflector)
    {
        return sprintf(
            '%s %s',
            self::formatModifiers($reflector),
            self::formatFunction($reflector)
        );
    }
    private static function formatFunctionParams(\ReflectionFunctionAbstract $reflector)
    {
        $params = array();
        foreach ($reflector->getParameters() as $param) {
            $hint = '';
            try {
                if ($param->isArray()) {
                    $hint = '<keyword>array</keyword> ';
                } elseif ($class = $param->getClass()) {
                    $hint = sprintf('<class>%s</class> ', $class->getName());
                }
            } catch (\Exception $e) {
                $chunks = explode('$' . $param->getName(), (string) $param);
                $chunks = explode(' ', trim($chunks[0]));
                $guess  = end($chunks);
                $hint = sprintf('<urgent>%s</urgent> ', $guess);
            }
            if ($param->isOptional()) {
                if (!$param->isDefaultValueAvailable()) {
                    $value     = 'unknown';
                    $typeStyle = 'urgent';
                } else {
                    $value     = $param->getDefaultValue();
                    $typeStyle = self::getTypeStyle($value);
                    $value     = is_array($value) ? 'array()' : is_null($value) ? 'null' : var_export($value, true);
                }
                $default   = sprintf(' = <%s>%s</%s>', $typeStyle, OutputFormatter::escape($value), $typeStyle);
            } else {
                $default = '';
            }
            $params[] = sprintf(
                '%s%s<strong>$%s</strong>%s',
                $param->isPassedByReference() ? '&' : '',
                $hint,
                $param->getName(),
                $default
            );
        }
        return $params;
    }
}
