<?php
if (!is_file(dirname(__DIR__) . '/vendor/autoload.php')) {
    throw new RuntimeException('Install dependencies to run test suite.');
}
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
$loader->add('Psy\\Test\\', __DIR__);
