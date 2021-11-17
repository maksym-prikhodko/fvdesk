<?php
class PHPUnit_Runner_Filter_Group_Exclude extends PHPUnit_Runner_Filter_GroupFilterIterator
{
    protected function doAccept($hash)
    {
        return !in_array($hash, $this->groupTests);
    }
}
