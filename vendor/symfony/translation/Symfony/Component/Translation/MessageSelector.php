<?php
namespace Symfony\Component\Translation;
class MessageSelector
{
    public function choose($message, $number, $locale)
    {
        $parts = explode('|', $message);
        $explicitRules = array();
        $standardRules = array();
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/^(?P<interval>'.Interval::getIntervalRegexp().')\s*(?P<message>.*?)$/x', $part, $matches)) {
                $explicitRules[$matches['interval']] = $matches['message'];
            } elseif (preg_match('/^\w+\:\s*(.*?)$/', $part, $matches)) {
                $standardRules[] = $matches[1];
            } else {
                $standardRules[] = $part;
            }
        }
        foreach ($explicitRules as $interval => $m) {
            if (Interval::test($number, $interval)) {
                return $m;
            }
        }
        $position = PluralizationRules::get($number, $locale);
        if (!isset($standardRules[$position])) {
            if (1 === count($parts) && isset($standardRules[0])) {
                return $standardRules[0];
            }
            throw new \InvalidArgumentException(sprintf('Unable to choose a translation for "%s" with locale "%s" for value "%d". Double check that this translation has the correct plural options (e.g. "There is one apple|There are %%count%% apples").', $message, $locale, $number));
        }
        return $standardRules[$position];
    }
}
