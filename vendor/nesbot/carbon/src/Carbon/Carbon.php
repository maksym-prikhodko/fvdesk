<?php
namespace Carbon;
use Closure;
use DateTime;
use DateTimeZone;
use DateInterval;
use DatePeriod;
use InvalidArgumentException;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Translation\Loader\ArrayLoader;
class Carbon extends DateTime
{
    const SUNDAY = 0;
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    protected static $days = array(
        self::SUNDAY => 'Sunday',
        self::MONDAY => 'Monday',
        self::TUESDAY => 'Tuesday',
        self::WEDNESDAY => 'Wednesday',
        self::THURSDAY => 'Thursday',
        self::FRIDAY => 'Friday',
        self::SATURDAY => 'Saturday',
    );
    protected static $relativeKeywords = array(
        'this',
        'next',
        'last',
        'tomorrow',
        'yesterday',
        '+',
        '-',
        'first',
        'last',
        'ago',
    );
    const YEARS_PER_CENTURY = 100;
    const YEARS_PER_DECADE = 10;
    const MONTHS_PER_YEAR = 12;
    const WEEKS_PER_YEAR = 52;
    const DAYS_PER_WEEK = 7;
    const HOURS_PER_DAY = 24;
    const MINUTES_PER_HOUR = 60;
    const SECONDS_PER_MINUTE = 60;
    const DEFAULT_TO_STRING_FORMAT = 'Y-m-d H:i:s';
    protected static $toStringFormat = self::DEFAULT_TO_STRING_FORMAT;
    protected static $testNow;
    protected static $translator;
    protected static function safeCreateDateTimeZone($object)
    {
        if ($object === null) {
            return new DateTimeZone(date_default_timezone_get());
        }
        if ($object instanceof DateTimeZone) {
            return $object;
        }
        $tz = @timezone_open((string) $object);
        if ($tz === false) {
            throw new InvalidArgumentException('Unknown or bad timezone ('.$object.')');
        }
        return $tz;
    }
    public function __construct($time = null, $tz = null)
    {
        if (static::hasTestNow() && (empty($time) || $time === 'now' || static::hasRelativeKeywords($time))) {
            $testInstance = clone static::getTestNow();
            if (static::hasRelativeKeywords($time)) {
                $testInstance->modify($time);
            }
            if ($tz !== NULL && $tz != static::getTestNow()->tz) {
                $testInstance->setTimezone($tz);
            } else {
                $tz = $testInstance->tz;
            }
            $time = $testInstance->toDateTimeString();
        }
        parent::__construct($time, static::safeCreateDateTimeZone($tz));
    }
    public static function instance(DateTime $dt)
    {
        return new static($dt->format('Y-m-d H:i:s.u'), $dt->getTimeZone());
    }
    public static function parse($time = null, $tz = null)
    {
        return new static($time, $tz);
    }
    public static function now($tz = null)
    {
        return new static(null, $tz);
    }
    public static function today($tz = null)
    {
        return static::now($tz)->startOfDay();
    }
    public static function tomorrow($tz = null)
    {
        return static::today($tz)->addDay();
    }
    public static function yesterday($tz = null)
    {
        return static::today($tz)->subDay();
    }
    public static function maxValue()
    {
        return static::createFromTimestamp(PHP_INT_MAX);
    }
    public static function minValue()
    {
        return static::createFromTimestamp(~PHP_INT_MAX);
    }
    public static function create($year = null, $month = null, $day = null, $hour = null, $minute = null, $second = null, $tz = null)
    {
        $year = ($year === null) ? date('Y') : $year;
        $month = ($month === null) ? date('n') : $month;
        $day = ($day === null) ? date('j') : $day;
        if ($hour === null) {
            $hour = date('G');
            $minute = ($minute === null) ? date('i') : $minute;
            $second = ($second === null) ? date('s') : $second;
        } else {
            $minute = ($minute === null) ? 0 : $minute;
            $second = ($second === null) ? 0 : $second;
        }
        return static::createFromFormat('Y-n-j G:i:s', sprintf('%s-%s-%s %s:%02s:%02s', $year, $month, $day, $hour, $minute, $second), $tz);
    }
    public static function createFromDate($year = null, $month = null, $day = null, $tz = null)
    {
        return static::create($year, $month, $day, null, null, null, $tz);
    }
    public static function createFromTime($hour = null, $minute = null, $second = null, $tz = null)
    {
        return static::create(null, null, null, $hour, $minute, $second, $tz);
    }
    public static function createFromFormat($format, $time, $tz = null)
    {
        if ($tz !== null) {
            $dt = parent::createFromFormat($format, $time, static::safeCreateDateTimeZone($tz));
        } else {
            $dt = parent::createFromFormat($format, $time);
        }
        if ($dt instanceof DateTime) {
            return static::instance($dt);
        }
        $errors = static::getLastErrors();
        throw new InvalidArgumentException(implode(PHP_EOL, $errors['errors']));
    }
    public static function createFromTimestamp($timestamp, $tz = null)
    {
        return static::now($tz)->setTimestamp($timestamp);
    }
    public static function createFromTimestampUTC($timestamp)
    {
        return new static('@'.$timestamp);
    }
    public function copy()
    {
        return static::instance($this);
    }
    public function __get($name)
    {
        switch (true) {
            case array_key_exists($name, $formats = array(
                'year' => 'Y',
                'yearIso' => 'o',
                'month' => 'n',
                'day' => 'j',
                'hour' => 'G',
                'minute' => 'i',
                'second' => 's',
                'micro' => 'u',
                'dayOfWeek' => 'w',
                'dayOfYear' => 'z',
                'weekOfYear' => 'W',
                'daysInMonth' => 't',
                'timestamp' => 'U',
            )):
                return (int) $this->format($formats[$name]);
            case $name === 'weekOfMonth':
                return (int) ceil($this->day / static::DAYS_PER_WEEK);
            case $name === 'age':
                return (int) $this->diffInYears();
            case $name === 'quarter':
                return (int) ceil($this->month / 3);
            case $name === 'offset':
                return $this->getOffset();
            case $name === 'offsetHours':
                return $this->getOffset() / static::SECONDS_PER_MINUTE / static::MINUTES_PER_HOUR;
            case $name === 'dst':
                return $this->format('I') == '1';
            case $name === 'local':
                return $this->offset == $this->copy()->setTimezone(date_default_timezone_get())->offset;
            case $name === 'utc':
                return $this->offset == 0;
            case $name === 'timezone' || $name === 'tz':
                return $this->getTimezone();
            case $name === 'timezoneName' || $name === 'tzName':
                return $this->getTimezone()->getName();
            default:
                throw new InvalidArgumentException(sprintf("Unknown getter '%s'", $name));
        }
    }
    public function __isset($name)
    {
        try {
            $this->__get($name);
        } catch (InvalidArgumentException $e) {
            return false;
        }
        return true;
    }
    public function __set($name, $value)
    {
        switch ($name) {
            case 'year':
                $this->setDate($value, $this->month, $this->day);
                break;
            case 'month':
                $this->setDate($this->year, $value, $this->day);
                break;
            case 'day':
                $this->setDate($this->year, $this->month, $value);
                break;
            case 'hour':
                $this->setTime($value, $this->minute, $this->second);
                break;
            case 'minute':
                $this->setTime($this->hour, $value, $this->second);
                break;
            case 'second':
                $this->setTime($this->hour, $this->minute, $value);
                break;
            case 'timestamp':
                parent::setTimestamp($value);
                break;
            case 'timezone':
            case 'tz':
                $this->setTimezone($value);
                break;
            default:
                throw new InvalidArgumentException(sprintf("Unknown setter '%s'", $name));
        }
    }
    public function year($value)
    {
        $this->year = $value;
        return $this;
    }
    public function month($value)
    {
        $this->month = $value;
        return $this;
    }
    public function day($value)
    {
        $this->day = $value;
        return $this;
    }
    public function hour($value)
    {
        $this->hour = $value;
        return $this;
    }
    public function minute($value)
    {
        $this->minute = $value;
        return $this;
    }
    public function second($value)
    {
        $this->second = $value;
        return $this;
    }
    public function setDateTime($year, $month, $day, $hour, $minute, $second = 0)
    {
        return $this->setDate($year, $month, $day)->setTime($hour, $minute, $second);
    }
    public function timestamp($value)
    {
        $this->timestamp = $value;
        return $this;
    }
    public function timezone($value)
    {
        return $this->setTimezone($value);
    }
    public function tz($value)
    {
        return $this->setTimezone($value);
    }
    public function setTimezone($value)
    {
        parent::setTimezone(static::safeCreateDateTimeZone($value));
        return $this;
    }
    public static function setTestNow(Carbon $testNow = null)
    {
        static::$testNow = $testNow;
    }
    public static function getTestNow()
    {
        return static::$testNow;
    }
    public static function hasTestNow()
    {
        return static::getTestNow() !== null;
    }
    public static function hasRelativeKeywords($time)
    {
        if (preg_match('/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/', $time) !== 1) {
            foreach (static::$relativeKeywords as $keyword) {
                if (stripos($time, $keyword) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
    protected static function translator()
    {
        if (static::$translator == null) {
            static::$translator = new Translator('en');
            static::$translator->addLoader('array', new ArrayLoader());
            static::setLocale('en');
        }
        return static::$translator;
    }
    public static function getTranslator()
    {
        return static::translator();
    }
    public static function setTranslator(TranslatorInterface $translator)
    {
        static::$translator = $translator;
    }
    public static function getLocale()
    {
        return static::translator()->getLocale();
    }
    public static function setLocale($locale)
    {
        static::translator()->setLocale($locale);
        static::translator()->addResource('array', require __DIR__.'/Lang/'.$locale.'.php', $locale);
    }
    public function formatLocalized($format)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
        }
        return strftime($format, strtotime($this));
    }
    public static function resetToStringFormat()
    {
        static::setToStringFormat(static::DEFAULT_TO_STRING_FORMAT);
    }
    public static function setToStringFormat($format)
    {
        static::$toStringFormat = $format;
    }
    public function __toString()
    {
        return $this->format(static::$toStringFormat);
    }
    public function toDateString()
    {
        return $this->format('Y-m-d');
    }
    public function toFormattedDateString()
    {
        return $this->format('M j, Y');
    }
    public function toTimeString()
    {
        return $this->format('H:i:s');
    }
    public function toDateTimeString()
    {
        return $this->format('Y-m-d H:i:s');
    }
    public function toDayDateTimeString()
    {
        return $this->format('D, M j, Y g:i A');
    }
    public function toAtomString()
    {
        return $this->format(static::ATOM);
    }
    public function toCookieString()
    {
        return $this->format(static::COOKIE);
    }
    public function toIso8601String()
    {
        return $this->format(static::ISO8601);
    }
    public function toRfc822String()
    {
        return $this->format(static::RFC822);
    }
    public function toRfc850String()
    {
        return $this->format(static::RFC850);
    }
    public function toRfc1036String()
    {
        return $this->format(static::RFC1036);
    }
    public function toRfc1123String()
    {
        return $this->format(static::RFC1123);
    }
    public function toRfc2822String()
    {
        return $this->format(static::RFC2822);
    }
    public function toRfc3339String()
    {
        return $this->format(static::RFC3339);
    }
    public function toRssString()
    {
        return $this->format(static::RSS);
    }
    public function toW3cString()
    {
        return $this->format(static::W3C);
    }
    public function eq(Carbon $dt)
    {
        return $this == $dt;
    }
    public function ne(Carbon $dt)
    {
        return !$this->eq($dt);
    }
    public function gt(Carbon $dt)
    {
        return $this > $dt;
    }
    public function gte(Carbon $dt)
    {
        return $this >= $dt;
    }
    public function lt(Carbon $dt)
    {
        return $this < $dt;
    }
    public function lte(Carbon $dt)
    {
        return $this <= $dt;
    }
    public function between(Carbon $dt1, Carbon $dt2, $equal = true)
    {
        if ($dt1->gt($dt2)) {
            $temp = $dt1;
            $dt1 = $dt2;
            $dt2 = $temp;
        }
        if ($equal) {
            return $this->gte($dt1) && $this->lte($dt2);
        } else {
            return $this->gt($dt1) && $this->lt($dt2);
        }
    }
    public function min(Carbon $dt = null)
    {
        $dt = ($dt === null) ? static::now($this->tz) : $dt;
        return $this->lt($dt) ? $this : $dt;
    }
    public function max(Carbon $dt = null)
    {
        $dt = ($dt === null) ? static::now($this->tz) : $dt;
        return $this->gt($dt) ? $this : $dt;
    }
    public function isWeekday()
    {
        return ($this->dayOfWeek != static::SUNDAY && $this->dayOfWeek != static::SATURDAY);
    }
    public function isWeekend()
    {
        return !$this->isWeekDay();
    }
    public function isYesterday()
    {
        return $this->toDateString() === static::yesterday($this->tz)->toDateString();
    }
    public function isToday()
    {
        return $this->toDateString() === static::now($this->tz)->toDateString();
    }
    public function isTomorrow()
    {
        return $this->toDateString() === static::tomorrow($this->tz)->toDateString();
    }
    public function isFuture()
    {
        return $this->gt(static::now($this->tz));
    }
    public function isPast()
    {
        return $this->lt(static::now($this->tz));
    }
    public function isLeapYear()
    {
        return $this->format('L') == '1';
    }
    public function isSameDay(Carbon $dt)
    {
        return $this->toDateString() === $dt->toDateString();
    }
    public function addYears($value)
    {
        return $this->modify((int) $value.' year');
    }
    public function addYear($value = 1)
    {
        return $this->addYears($value);
    }
    public function subYear($value = 1)
    {
        return $this->subYears($value);
    }
    public function subYears($value)
    {
        return $this->addYears(-1 * $value);
    }
    public function addMonths($value)
    {
        return $this->modify((int) $value.' month');
    }
    public function addMonth($value = 1)
    {
        return $this->addMonths($value);
    }
    public function subMonth($value = 1)
    {
        return $this->subMonths($value);
    }
    public function subMonths($value)
    {
        return $this->addMonths(-1 * $value);
    }
    public function addMonthsNoOverflow($value)
    {
        $date = $this->copy()->addMonths($value);
        if ($date->day != $this->day) {
            $date->day(1)->subMonth()->day($date->daysInMonth);
        }
        return $date;
    }
    public function addMonthNoOverflow($value = 1)
    {
        return $this->addMonthsNoOverflow($value);
    }
    public function subMonthNoOverflow($value = 1)
    {
        return $this->subMonthsNoOverflow($value);
    }
    public function subMonthsNoOverflow($value)
    {
        return $this->addMonthsNoOverflow(-1 * $value);
    }
    public function addDays($value)
    {
        return $this->modify((int) $value.' day');
    }
    public function addDay($value = 1)
    {
        return $this->addDays($value);
    }
    public function subDay($value = 1)
    {
        return $this->subDays($value);
    }
    public function subDays($value)
    {
        return $this->addDays(-1 * $value);
    }
    public function addWeekdays($value)
    {
        return $this->modify((int) $value.' weekday');
    }
    public function addWeekday($value = 1)
    {
        return $this->addWeekdays($value);
    }
    public function subWeekday($value = 1)
    {
        return $this->subWeekdays($value);
    }
    public function subWeekdays($value)
    {
        return $this->addWeekdays(-1 * $value);
    }
    public function addWeeks($value)
    {
        return $this->modify((int) $value.' week');
    }
    public function addWeek($value = 1)
    {
        return $this->addWeeks($value);
    }
    public function subWeek($value = 1)
    {
        return $this->subWeeks($value);
    }
    public function subWeeks($value)
    {
        return $this->addWeeks(-1 * $value);
    }
    public function addHours($value)
    {
        return $this->modify((int) $value.' hour');
    }
    public function addHour($value = 1)
    {
        return $this->addHours($value);
    }
    public function subHour($value = 1)
    {
        return $this->subHours($value);
    }
    public function subHours($value)
    {
        return $this->addHours(-1 * $value);
    }
    public function addMinutes($value)
    {
        return $this->modify((int) $value.' minute');
    }
    public function addMinute($value = 1)
    {
        return $this->addMinutes($value);
    }
    public function subMinute($value = 1)
    {
        return $this->subMinutes($value);
    }
    public function subMinutes($value)
    {
        return $this->addMinutes(-1 * $value);
    }
    public function addSeconds($value)
    {
        return $this->modify((int) $value.' second');
    }
    public function addSecond($value = 1)
    {
        return $this->addSeconds($value);
    }
    public function subSecond($value = 1)
    {
        return $this->subSeconds($value);
    }
    public function subSeconds($value)
    {
        return $this->addSeconds(-1 * $value);
    }
    public function diffInYears(Carbon $dt = null, $abs = true)
    {
        $dt = ($dt === null) ? static::now($this->tz) : $dt;
        return (int) $this->diff($dt, $abs)->format('%r%y');
    }
    public function diffInMonths(Carbon $dt = null, $abs = true)
    {
        $dt = ($dt === null) ? static::now($this->tz) : $dt;
        return $this->diffInYears($dt, $abs) * static::MONTHS_PER_YEAR + (int) $this->diff($dt, $abs)->format('%r%m');
    }
    public function diffInWeeks(Carbon $dt = null, $abs = true)
    {
        return (int) ($this->diffInDays($dt, $abs) / static::DAYS_PER_WEEK);
    }
    public function diffInDays(Carbon $dt = null, $abs = true)
    {
        $dt = ($dt === null) ? static::now($this->tz) : $dt;
        return (int) $this->diff($dt, $abs)->format('%r%a');
    }
    public function diffInDaysFiltered(Closure $callback, Carbon $dt = null, $abs = true)
    {
        return $this->diffFiltered(CarbonInterval::day(), $callback, $dt, $abs);
    }
    public function diffInHoursFiltered(Closure $callback, Carbon $dt = null, $abs = true)
    {
        return $this->diffFiltered(CarbonInterval::hour(), $callback, $dt, $abs);
    }
    public function diffFiltered(CarbonInterval $ci, Closure $callback, Carbon $dt = null, $abs = true)
    {
        $start = $this;
        $end = ($dt === null) ? static::now($this->tz) : $dt;
        $inverse = false;
        if ($end < $start) {
            $start = $end;
            $end = $this;
            $inverse = true;
        }
        $period = new DatePeriod($start, $ci, $end);
        $vals = array_filter(iterator_to_array($period), function (DateTime $date) use ($callback) {
            return call_user_func($callback, Carbon::instance($date));
        });
        $diff = count($vals);
        return $inverse && !$abs ? -$diff : $diff;
    }
    public function diffInWeekdays(Carbon $dt = null, $abs = true)
    {
        return $this->diffInDaysFiltered(function (Carbon $date) {
            return $date->isWeekday();
        }, $dt, $abs);
    }
    public function diffInWeekendDays(Carbon $dt = null, $abs = true)
    {
        return $this->diffInDaysFiltered(function (Carbon $date) {
            return $date->isWeekend();
        }, $dt, $abs);
    }
    public function diffInHours(Carbon $dt = null, $abs = true)
    {
        return (int) ($this->diffInSeconds($dt, $abs) / static::SECONDS_PER_MINUTE / static::MINUTES_PER_HOUR);
    }
    public function diffInMinutes(Carbon $dt = null, $abs = true)
    {
        return (int) ($this->diffInSeconds($dt, $abs) / static::SECONDS_PER_MINUTE);
    }
    public function diffInSeconds(Carbon $dt = null, $abs = true)
    {
        $dt = ($dt === null) ? static::now($this->tz) : $dt;
        $value = $dt->getTimestamp() - $this->getTimestamp();
        return $abs ? abs($value) : $value;
    }
    public function secondsSinceMidnight()
    {
        return $this->diffInSeconds($this->copy()->startOfDay());
    }
    public function secondsUntilEndOfDay()
    {
        return $this->diffInSeconds($this->copy()->endOfDay());
    }
    public function diffForHumans(Carbon $other = null, $absolute = false)
    {
        $isNow = $other === null;
        if ($isNow) {
            $other = static::now($this->tz);
        }
        $diffInterval = $this->diff($other);
        switch (true) {
            case ($diffInterval->y > 0):
                $unit = 'year';
                $count = $diffInterval->y;
                break;
            case ($diffInterval->m > 0):
                $unit = 'month';
                $count = $diffInterval->m;
                break;
            case ($diffInterval->d > 0):
                $unit = 'day';
                $count = $diffInterval->d;
                if ($count >= self::DAYS_PER_WEEK) {
                    $unit = 'week';
                    $count = (int) ($count / self::DAYS_PER_WEEK);
                }
                break;
            case ($diffInterval->h > 0):
                $unit = 'hour';
                $count = $diffInterval->h;
                break;
            case ($diffInterval->i > 0):
                $unit = 'minute';
                $count = $diffInterval->i;
                break;
            default:
                $count = $diffInterval->s;
                $unit = 'second';
                break;
        }
        if ($count == 0) {
            $count = 1;
        }
        $time = static::translator()->transChoice($unit, $count, array(':count' => $count));
        if ($absolute) {
            return $time;
        }
        $isFuture = $diffInterval->invert === 1;
        $transId = $isNow ? ($isFuture ? 'from_now' : 'ago') : ($isFuture ? 'after' : 'before');
        $tryKeyExists = $unit.'_'.$transId;
        if ($tryKeyExists !== static::translator()->transChoice($tryKeyExists, $count)) {
            $time = static::translator()->transChoice($tryKeyExists, $count, array(':count' => $count));
        }
        return static::translator()->trans($transId, array(':time' => $time));
    }
    public function startOfDay()
    {
        return $this->hour(0)->minute(0)->second(0);
    }
    public function endOfDay()
    {
        return $this->hour(23)->minute(59)->second(59);
    }
    public function startOfMonth()
    {
        return $this->startOfDay()->day(1);
    }
    public function endOfMonth()
    {
        return $this->day($this->daysInMonth)->endOfDay();
    }
    public function startOfYear()
    {
        return $this->month(1)->startOfMonth();
    }
    public function endOfYear()
    {
        return $this->month(static::MONTHS_PER_YEAR)->endOfMonth();
    }
    public function startOfDecade()
    {
        return $this->startOfYear()->year($this->year - $this->year % static::YEARS_PER_DECADE);
    }
    public function endOfDecade()
    {
        return $this->endOfYear()->year($this->year - $this->year % static::YEARS_PER_DECADE + static::YEARS_PER_DECADE - 1);
    }
    public function startOfCentury()
    {
        return $this->startOfYear()->year($this->year - $this->year % static::YEARS_PER_CENTURY);
    }
    public function endOfCentury()
    {
        return $this->endOfYear()->year($this->year - $this->year % static::YEARS_PER_CENTURY + static::YEARS_PER_CENTURY - 1);
    }
    public function startOfWeek()
    {
        if ($this->dayOfWeek != static::MONDAY) {
            $this->previous(static::MONDAY);
        }
        return $this->startOfDay();
    }
    public function endOfWeek()
    {
        if ($this->dayOfWeek != static::SUNDAY) {
            $this->next(static::SUNDAY);
        }
        return $this->endOfDay();
    }
    public function next($dayOfWeek = null)
    {
        if ($dayOfWeek === null) {
            $dayOfWeek = $this->dayOfWeek;
        }
        return $this->startOfDay()->modify('next '.static::$days[$dayOfWeek]);
    }
    public function previous($dayOfWeek = null)
    {
        if ($dayOfWeek === null) {
            $dayOfWeek = $this->dayOfWeek;
        }
        return $this->startOfDay()->modify('last '.static::$days[$dayOfWeek]);
    }
    public function firstOfMonth($dayOfWeek = null)
    {
        $this->startOfDay();
        if ($dayOfWeek === null) {
            return $this->day(1);
        }
        return $this->modify('first '.static::$days[$dayOfWeek].' of '.$this->format('F').' '.$this->year);
    }
    public function lastOfMonth($dayOfWeek = null)
    {
        $this->startOfDay();
        if ($dayOfWeek === null) {
            return $this->day($this->daysInMonth);
        }
        return $this->modify('last '.static::$days[$dayOfWeek].' of '.$this->format('F').' '.$this->year);
    }
    public function nthOfMonth($nth, $dayOfWeek)
    {
        $dt = $this->copy()->firstOfMonth();
        $check = $dt->format('Y-m');
        $dt->modify('+'.$nth.' '.static::$days[$dayOfWeek]);
        return ($dt->format('Y-m') === $check) ? $this->modify($dt) : false;
    }
    public function firstOfQuarter($dayOfWeek = null)
    {
        return $this->day(1)->month($this->quarter * 3 - 2)->firstOfMonth($dayOfWeek);
    }
    public function lastOfQuarter($dayOfWeek = null)
    {
        return $this->day(1)->month($this->quarter * 3)->lastOfMonth($dayOfWeek);
    }
    public function nthOfQuarter($nth, $dayOfWeek)
    {
        $dt = $this->copy()->day(1)->month($this->quarter * 3);
        $last_month = $dt->month;
        $year = $dt->year;
        $dt->firstOfQuarter()->modify('+'.$nth.' '.static::$days[$dayOfWeek]);
        return ($last_month < $dt->month || $year !== $dt->year) ? false : $this->modify($dt);
    }
    public function firstOfYear($dayOfWeek = null)
    {
        return $this->month(1)->firstOfMonth($dayOfWeek);
    }
    public function lastOfYear($dayOfWeek = null)
    {
        return $this->month(static::MONTHS_PER_YEAR)->lastOfMonth($dayOfWeek);
    }
    public function nthOfYear($nth, $dayOfWeek)
    {
        $dt = $this->copy()->firstOfYear()->modify('+'.$nth.' '.static::$days[$dayOfWeek]);
        return $this->year == $dt->year ? $this->modify($dt) : false;
    }
    public function average(Carbon $dt = null)
    {
        $dt = ($dt === null) ? static::now($this->tz) : $dt;
        return $this->addSeconds((int) ($this->diffInSeconds($dt, false) / 2));
    }
    public function isBirthday(Carbon $dt)
    {
        return $this->format('md') === $dt->format('md');
    }
}
