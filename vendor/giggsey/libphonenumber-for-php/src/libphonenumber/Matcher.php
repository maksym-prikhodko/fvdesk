<?php
namespace libphonenumber;
class Matcher
{
    private $pattern;
    private $subject;
    private $groups = array();
    public function __construct($pattern, $subject)
    {
        $this->pattern = str_replace('/', '\/', $pattern);
        $this->subject = $subject;
    }
    private function doMatch($type = 'find')
    {
        $final_pattern = '(?:' . $this->pattern . ')';
        switch ($type) {
            case 'matches':
                $final_pattern = '^' . $final_pattern . '$';
                break;
            case 'lookingAt':
                $final_pattern = '^' . $final_pattern;
                break;
            case 'find':
            default:
                break;
        }
        $final_pattern = '/' . $final_pattern . '/x';
        return (preg_match($final_pattern, $this->subject, $this->groups, PREG_OFFSET_CAPTURE) == 1) ? true : false;
    }
    public function matches()
    {
        return $this->doMatch('matches');
    }
    public function lookingAt()
    {
        return $this->doMatch('lookingAt');
    }
    public function find()
    {
        return $this->doMatch('find');
    }
    public function groupCount()
    {
        if (empty($this->groups)) {
            return null;
        } else {
            return count($this->groups) - 1;
        }
    }
    public function group($group = null)
    {
        if (!isset($group)) {
            $group = 0;
        }
        return (isset($this->groups[$group][0])) ? $this->groups[$group][0] : null;
    }
    public function end($group = null)
    {
        if (!isset($group) || $group === null) {
            $group = 0;
        }
        if (!isset($this->groups[$group])) {
            return null;
        }
        return $this->groups[$group][1] + strlen($this->groups[$group][0]);
    }
    public function start($group = null)
    {
        if (isset($group) || $group === null) {
            $group = 0;
        }
        if (!isset($this->groups[$group])) {
            return null;
        }
        return $this->groups[$group][1];
    }
    public function replaceFirst($replacement)
    {
        return preg_replace('/' . $this->pattern . '/x', $replacement, $this->subject, 1);
    }
    public function replaceAll($replacement)
    {
        return preg_replace('/' . $this->pattern . '/x', $replacement, $this->subject);
    }
    public function reset($input = "")
    {
        $this->subject = $input;
        return $this;
    }
}
