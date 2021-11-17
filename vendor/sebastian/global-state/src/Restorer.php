<?php
namespace SebastianBergmann\GlobalState;
use ReflectionProperty;
class Restorer
{
    public function restoreFunctions(Snapshot $snapshot)
    {
        if (!function_exists('uopz_delete')) {
            throw new RuntimeException('The uopz_delete() function is required for this operation');
        }
        $functions = get_defined_functions();
        foreach (array_diff($functions['user'], $snapshot->functions()) as $function) {
            uopz_delete($function);
        }
    }
    public function restoreGlobalVariables(Snapshot $snapshot)
    {
        $superGlobalArrays = $snapshot->superGlobalArrays();
        foreach ($superGlobalArrays as $superGlobalArray) {
            $this->restoreSuperGlobalArray($snapshot, $superGlobalArray);
        }
        $globalVariables = $snapshot->globalVariables();
        foreach (array_keys($GLOBALS) as $key) {
            if ($key != 'GLOBALS' &&
                !in_array($key, $superGlobalArrays) &&
                !$snapshot->blacklist()->isGlobalVariableBlacklisted($key)) {
                if (isset($globalVariables[$key])) {
                    $GLOBALS[$key] = $globalVariables[$key];
                } else {
                    unset($GLOBALS[$key]);
                }
            }
        }
    }
    public function restoreStaticAttributes(Snapshot $snapshot)
    {
        foreach ($snapshot->staticAttributes() as $className => $staticAttributes) {
            foreach ($staticAttributes as $name => $value) {
                $reflector = new ReflectionProperty($className, $name);
                $reflector->setAccessible(true);
                $reflector->setValue($value);
            }
        }
    }
    private function restoreSuperGlobalArray(Snapshot $snapshot, $superGlobalArray)
    {
        $superGlobalVariables = $snapshot->superGlobalVariables();
        if (isset($GLOBALS[$superGlobalArray]) &&
            is_array($GLOBALS[$superGlobalArray]) &&
            isset($superGlobalVariables[$superGlobalArray])) {
            $keys = array_keys(
                array_merge(
                    $GLOBALS[$superGlobalArray],
                    $superGlobalVariables[$superGlobalArray]
                )
            );
            foreach ($keys as $key) {
                if (isset($superGlobalVariables[$superGlobalArray][$key])) {
                    $GLOBALS[$superGlobalArray][$key] = $superGlobalVariables[$superGlobalArray][$key];
                } else {
                    unset($GLOBALS[$superGlobalArray][$key]);
                }
            }
        }
    }
}
