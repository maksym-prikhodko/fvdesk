<?php
namespace Psy\ExecutionLoop;
use Psy\Configuration;
use Psy\Exception\BreakException;
use Psy\Exception\ThrowUpException;
use Psy\Shell;
class Loop
{
    public function __construct(Configuration $config)
    {
    }
    public function run(Shell $shell)
    {
        $loop = function ($__psysh__) {
            set_error_handler(array($__psysh__, 'handleError'));
            try {
                foreach ($__psysh__->getIncludes() as $__psysh_include__) {
                    include $__psysh_include__;
                }
            } catch (\Exception $_e) {
                $__psysh__->writeException($_e);
            }
            restore_error_handler();
            unset($__psysh_include__);
            extract($__psysh__->getScopeVariables());
            do {
                $__psysh__->beforeLoop();
                $__psysh__->setScopeVariables(get_defined_vars());
                try {
                    $__psysh__->getInput();
                    ob_start(
                        array($__psysh__, 'writeStdout'),
                        version_compare(PHP_VERSION, '5.4', '>=') ? 1 : 2
                    );
                    set_error_handler(array($__psysh__, 'handleError'));
                    $_ = eval($__psysh__->flushCode());
                    restore_error_handler();
                    ob_end_flush();
                    $__psysh__->writeReturnValue($_);
                } catch (BreakException $_e) {
                    restore_error_handler();
                    if (ob_get_level() > 0) {
                        ob_end_clean();
                    }
                    $__psysh__->writeException($_e);
                    return;
                } catch (ThrowUpException $_e) {
                    restore_error_handler();
                    if (ob_get_level() > 0) {
                        ob_end_clean();
                    }
                    $__psysh__->writeException($_e);
                    throw $_e;
                } catch (\Exception $_e) {
                    restore_error_handler();
                    if (ob_get_level() > 0) {
                        ob_end_clean();
                    }
                    $__psysh__->writeException($_e);
                }
                unset($__psysh_out__);
                $__psysh__->afterLoop();
            } while (true);
        };
        if (self::bindLoop()) {
            $that = null;
            try {
                $that = $shell->getScopeVariable('this');
            } catch (\InvalidArgumentException $e) {
            }
            if (is_object($that)) {
                $loop = $loop->bindTo($that, get_class($that));
            } else {
                $loop = $loop->bindTo(null, null);
            }
        }
        $loop($shell);
    }
    public function beforeLoop()
    {
    }
    public function afterLoop()
    {
    }
    protected static function bindLoop()
    {
        if (defined('HHVM_VERSION')) {
            return version_compare(HHVM_VERSION, '3.5.0', '>=');
        }
        return version_compare(PHP_VERSION, '5.4', '>=');
    }
}
