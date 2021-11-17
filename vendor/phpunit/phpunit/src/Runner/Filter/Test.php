<?php
class PHPUnit_Runner_Filter_Test extends RecursiveFilterIterator
{
    protected $filter = null;
    protected $filterMin;
    protected $filterMax;
    public function __construct(RecursiveIterator $iterator, $filter)
    {
        parent::__construct($iterator);
        $this->setFilter($filter);
    }
    protected function setFilter($filter)
    {
        if (PHPUnit_Util_Regex::pregMatchSafe($filter, '') === false) {
            if (preg_match('/^(.*?)#(\d+)(?:-(\d+))?$/', $filter, $matches)) {
                if (isset($matches[3]) && $matches[2] < $matches[3]) {
                    $filter = sprintf(
                        '%s.*with data set #(\d+)$',
                        $matches[1]
                    );
                    $this->filterMin = $matches[2];
                    $this->filterMax = $matches[3];
                } else {
                    $filter = sprintf(
                        '%s.*with data set #%s$',
                        $matches[1],
                        $matches[2]
                    );
                }
            } 
            elseif (preg_match('/^(.*?)@(.+)$/', $filter, $matches)) {
                $filter = sprintf(
                    '%s.*with data set "%s"$',
                    $matches[1],
                    $matches[2]
                );
            }
            $filter = sprintf('/%s/', str_replace(
                '/',
                '\\/',
                $filter
            ));
        }
        $this->filter = $filter;
    }
    public function accept()
    {
        $test = $this->getInnerIterator()->current();
        if ($test instanceof PHPUnit_Framework_TestSuite) {
            return true;
        }
        $tmp = PHPUnit_Util_Test::describe($test, false);
        if ($tmp[0] != '') {
            $name = implode('::', $tmp);
        } else {
            $name = $tmp[1];
        }
        $accepted = preg_match($this->filter, $name, $matches);
        if ($accepted && isset($this->filterMax)) {
            $set = end($matches);
            $accepted = $set >= $this->filterMin && $set <= $this->filterMax;
        }
        return $accepted;
    }
}
