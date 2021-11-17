<?php
if (class_exists('Swift', false)) {
    return;
}
require dirname(__FILE__).'/Swift.php';
if (!function_exists('_swiftmailer_init')) {
    function _swiftmailer_init()
    {
        require dirname(__FILE__).'/swift_init.php';
    }
}
Swift::registerAutoload('_swiftmailer_init');
