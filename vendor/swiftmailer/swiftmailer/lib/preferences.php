<?php
$preferences = Swift_Preferences::getInstance();
$preferences->setCharset('utf-8');
if (@is_writable($tmpDir = sys_get_temp_dir())) {
    $preferences->setTempDir($tmpDir)->setCacheType('disk');
}
if (version_compare(phpversion(), '5.4.7', '<')) {
    $preferences->setQPDotEscape(false);
}
