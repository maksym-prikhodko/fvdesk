<?php
class PHP_CodeCoverage_Report_XML_Node
{
    private $dom;
    private $contextNode;
    public function __construct(DOMElement $context)
    {
        $this->setContextNode($context);
    }
    protected function setContextNode(DOMElement $context)
    {
        $this->dom         = $context->ownerDocument;
        $this->contextNode = $context;
    }
    public function getDom()
    {
        return $this->dom;
    }
    protected function getContextNode()
    {
        return $this->contextNode;
    }
    public function getTotals()
    {
        $totalsContainer = $this->getContextNode()->firstChild;
        if (!$totalsContainer) {
            $totalsContainer = $this->getContextNode()->appendChild(
                $this->dom->createElementNS(
                    'http:
                    'totals'
                )
            );
        }
        return new PHP_CodeCoverage_Report_XML_Totals($totalsContainer);
    }
    public function addDirectory($name)
    {
        $dirNode = $this->getDom()->createElementNS(
            'http:
            'directory'
        );
        $dirNode->setAttribute('name', $name);
        $this->getContextNode()->appendChild($dirNode);
        return new PHP_CodeCoverage_Report_XML_Directory($dirNode);
    }
    public function addFile($name, $href)
    {
        $fileNode = $this->getDom()->createElementNS(
            'http:
            'file'
        );
        $fileNode->setAttribute('name', $name);
        $fileNode->setAttribute('href', $href);
        $this->getContextNode()->appendChild($fileNode);
        return new PHP_CodeCoverage_Report_XML_File($fileNode);
    }
}
