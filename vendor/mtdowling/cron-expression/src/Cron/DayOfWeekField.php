<?php
namespace Cron;
class DayOfWeekField extends AbstractField
{
    public function isSatisfiedBy(\DateTime $date, $value)
    {
        if ($value == '?') {
            return true;
        }
        $value = $this->convertLiterals($value);
        $currentYear = $date->format('Y');
        $currentMonth = $date->format('m');
        $lastDayOfMonth = $date->format('t');
        if (strpos($value, 'L')) {
            $weekday = str_replace('7', '0', substr($value, 0, strpos($value, 'L')));
            $tdate = clone $date;
            $tdate->setDate($currentYear, $currentMonth, $lastDayOfMonth);
            while ($tdate->format('w') != $weekday) {
                $tdate->setDate($currentYear, $currentMonth, --$lastDayOfMonth);
            }
            return $date->format('j') == $lastDayOfMonth;
        }
        if (strpos($value, '#')) {
            list($weekday, $nth) = explode('#', $value);
            if ($weekday === '0') {
                $weekday = 7;
            }
            if ($weekday < 0 || $weekday > 7) {
                throw new \InvalidArgumentException("Weekday must be a value between 0 and 7. {$weekday} given");
            }
            if ($nth > 5) {
                throw new \InvalidArgumentException('There are never more than 5 of a given weekday in a month');
            }
            if ($date->format('N') != $weekday) {
                return false;
            }
            $tdate = clone $date;
            $tdate->setDate($currentYear, $currentMonth, 1);
            $dayCount = 0;
            $currentDay = 1;
            while ($currentDay < $lastDayOfMonth + 1) {
                if ($tdate->format('N') == $weekday) {
                    if (++$dayCount >= $nth) {
                        break;
                    }
                }
                $tdate->setDate($currentYear, $currentMonth, ++$currentDay);
            }
            return $date->format('j') == $currentDay;
        }
        if (strpos($value, '-')) {
            $parts = explode('-', $value);
            if ($parts[0] == '7') {
                $parts[0] = '0';
            } elseif ($parts[1] == '0') {
                $parts[1] = '7';
            }
            $value = implode('-', $parts);
        }
        $format = in_array(7, str_split($value)) ? 'N' : 'w';
        $fieldValue = $date->format($format);
        return $this->isSatisfied($fieldValue, $value);
    }
    public function increment(\DateTime $date, $invert = false)
    {
        if ($invert) {
            $date->modify('-1 day');
            $date->setTime(23, 59, 0);
        } else {
            $date->modify('+1 day');
            $date->setTime(0, 0, 0);
        }
        return $this;
    }
    public function validate($value)
    {
        $value = $this->convertLiterals($value);
        foreach (explode(',', $value) as $expr) {
            if (!preg_match('/^(\*|[0-7](L?|#[1-5]))([\/\,\-][0-7]+)*$/', $expr)) {
                return false;
            }
        }
        return true;
    }
    private function convertLiterals($string)
    {
        return str_ireplace(
            array('SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'),
            range(0, 6),
            $string
        );
    }
}