<?php
class PHP_Timer
{
    private static $times = array(
      'hour'   => 3600000,
      'minute' => 60000,
      'second' => 1000
    );
    private static $startTimes = array();
    public static $requestTime;
    public static function start()
    {
        array_push(self::$startTimes, microtime(TRUE));
    }
    public static function stop()
    {
        return microtime(TRUE) - array_pop(self::$startTimes);
    }
    public static function secondsToTimeString($time)
    {
        $ms = round($time * 1000);
        foreach (self::$times as $unit => $value) {
            if ($ms >= $value) {
                $time = floor($ms / $value * 100.0) / 100.0;
                return $time . ' ' . ($time == 1 ? $unit : $unit . 's');
            }
        }
        return $ms . ' ms';
    }
    public static function timeSinceStartOfRequest()
    {
        return self::secondsToTimeString(microtime(TRUE) - self::$requestTime);
    }
    public static function resourceUsage()
    {
        return sprintf(
          'Time: %s, Memory: %4.2fMb',
          self::timeSinceStartOfRequest(),
          memory_get_peak_usage(TRUE) / 1048576
        );
    }
}
if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
    PHP_Timer::$requestTime = $_SERVER['REQUEST_TIME_FLOAT'];
}
else {
    PHP_Timer::$requestTime = microtime(TRUE);
}
