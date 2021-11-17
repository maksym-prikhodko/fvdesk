<?php
declare (ticks = 1);
pcntl_signal(SIGUSR1, function () {echo 'Caught SIGUSR1'; exit;});
$n = 0;
while ($n < 400) {
    usleep(10000);
    $n++;
}
return;
