<?php
class PHP_CodeCoverage_Report_XML_File
{
    protected $dom;
    protected $contextNode;
    public function __construct(DOMElement $context)
    {
        $this->dom         = $context->ownerDocument;
        $this->contextNode = $context;
    }
    public function getTotals()
    {
        $totalsContainer = $this->contextNode->firstChild;
        if (!$totalsContainer) {
            $totalsContainer = $this->contextNode->appendChild(
                $this->dom->createElementNS(
                    'http:
                    'totals'
                )
            );
        }
        return new PHP_CodeCoverage_Report_XML_Totals($totalsContainer);
    }
    public function getLineCoverage($line)
    {
        $coverage = $this->contextNode->getElementsByTagNameNS(
            'http:
            'coverage'
        )->item(0);
        if (!$coverage) {
            $coverage = $this->contextNode->appendChild(
                $this->dom->createElementNS(
                    'http:
                    'coverage'
                )
            );
        }
        $lineNode = $coverage->appendChild(
            $this->dom->createElementNS(
                'http:
                'line'
            )
        );
        return new PHP_CodeCoverage_Report_XML_File_Coverage($lineNode, $line);
    }
}
