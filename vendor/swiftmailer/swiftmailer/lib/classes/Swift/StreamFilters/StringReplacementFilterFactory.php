<?php
class Swift_StreamFilters_StringReplacementFilterFactory implements Swift_ReplacementFilterFactory
{
    private $_filters = array();
    public function createFilter($search, $replace)
    {
        if (!isset($this->_filters[$search][$replace])) {
            if (!isset($this->_filters[$search])) {
                $this->_filters[$search] = array();
            }
            if (!isset($this->_filters[$search][$replace])) {
                $this->_filters[$search][$replace] = array();
            }
            $this->_filters[$search][$replace] = new Swift_StreamFilters_StringReplacementFilter($search, $replace);
        }
        return $this->_filters[$search][$replace];
    }
}
