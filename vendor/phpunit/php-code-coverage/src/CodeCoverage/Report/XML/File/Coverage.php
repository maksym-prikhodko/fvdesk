<?php
class PHP_CodeCoverage_Report_XML_File_Coverage
{
    private $writer;
    private $contextNode;
    private $finalized = false;
    public function __construct(DOMElement $context, $line)
    {
        $this->contextNode = $context;
        $this->writer = new XMLWriter();
        $this->writer->openMemory();
        $this->writer->startElementNs(null, $context->nodeName, 'http:
        $this->writer->writeAttribute('nr', $line);
    }
    public function addTest($test)
    {
        if ($this->finalized) {
            throw new PHP_CodeCoverage_Exception('Coverage Report already finalized');
        }
        $this->writer->startElement('covered');
        $this->writer->writeAttribute('by', $test);
        $this->writer->endElement();
    }
    public function finalize()
    {
        $this->writer->endElement();
        $fragment = $this->contextNode->ownerDocument->createDocumentFragment();
        $fragment->appendXML($this->writer->outputMemory());
        $this->contextNode->parentNode->replaceChild(
            $fragment,
            $this->contextNode
        );
        $this->finalized = true;
    }
}
