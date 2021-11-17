<?php
class PHPUnit_Framework_Constraint_IsJson extends PHPUnit_Framework_Constraint
{
    protected function matches($other)
    {
        json_decode($other);
        if (json_last_error()) {
            return false;
        }
        return true;
    }
    protected function failureDescription($other)
    {
        json_decode($other);
        $error = PHPUnit_Framework_Constraint_JsonMatches_ErrorMessageProvider::determineJsonError(
            json_last_error()
        );
        return sprintf(
            '%s is valid JSON (%s)',
            $this->exporter->shortenedExport($other),
            $error
        );
    }
    public function toString()
    {
        return 'is valid JSON';
    }
}
