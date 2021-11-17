<?php
error_reporting(-1);
date_default_timezone_set('UTC');
$vendorPos = strpos(__DIR__, 'vendor/vlucas/phpdotenv');
if($vendorPos !== false) {
    $vendorDir = substr(__DIR__, 0, $vendorPos) . 'vendor/';
    $loader = require $vendorDir . 'autoload.php';
} else {
    $loader = require __DIR__.'/../vendor/autoload.php';
}
