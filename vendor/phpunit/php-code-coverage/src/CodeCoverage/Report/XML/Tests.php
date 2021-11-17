<?php
class PHP_CodeCoverage_Report_XML_Tests
{
    private $contextNode;
    private $codeMap = array(
        0 => 'PASSED',     
        1 => 'SKIPPED',    
        2 => 'INCOMPLETE', 
        3 => 'FAILURE',    
        4 => 'ERROR',      
        5 => 'RISKY'       
    );
    public function __construct(DOMElement $context)
    {
        $this->contextNode = $context;
    }
    public function addTest($test, $result)
    {
        $node = $this->contextNode->appendChild(
            $this->contextNode->ownerDocument->createElementNS(
                'http:
                'test'
            )
        );
        $node->setAttribute('name', $test);
        $node->setAttribute('result', (int) $result);
        $node->setAttribute('status', $this->codeMap[(int) $result]);
    }
}
