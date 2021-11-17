<?php
namespace Stringy;
if (!function_exists('Stringy\create')) {
    function create($str, $encoding = null)
    {
        return new Stringy($str, $encoding);
    }
}
