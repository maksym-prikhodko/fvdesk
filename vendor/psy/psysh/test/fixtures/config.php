<?php
$config->setRuntimeDir(sys_get_temp_dir() . '/psysh_test/withconfig/temp');
return array(
    'useReadline' => true,
    'usePcntl'    => false,
);
