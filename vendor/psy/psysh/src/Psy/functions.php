<?php
namespace Psy;
if (!function_exists('Psy\sh')) {
    function sh()
    {
        return 'extract(\Psy\Shell::debug(get_defined_vars(), isset($this) ? $this : null));';
    }
}
