<?php
namespace PhpParser\Unserializer;
use PhpParser\Node\Scalar;
use PhpParser\Comment;
class XMLTest extends \PHPUnit_Framework_TestCase
{
    public function testNode() {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<AST xmlns:node="http:
 <node:Scalar_String line="1" docComment="">
  <attribute:startLine>
   <scalar:int>1</scalar:int>
  </attribute:startLine>
  <attribute:comments>
   <scalar:array>
    <comment isDocComment="false" line="2">
</comment>
    <comment isDocComment="true" line="3"></comment>
   </scalar:array>
  </attribute:comments>
  <subNode:value>
   <scalar:string>Test</scalar:string>
  </subNode:value>
 </node:Scalar_String>
</AST>
XML;
        $unserializer  = new XML;
        $this->assertEquals(
            new Scalar\String_('Test', array(
                'startLine' => 1,
                'comments'  => array(
                    new Comment('
                    new Comment\Doc('', 3),
                ),
            )),
            $unserializer->unserialize($xml)
        );
    }
    public function testEmptyNode() {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<AST xmlns:node="http:
 <node:Scalar_MagicConst_Class />
</AST>
XML;
        $unserializer  = new XML;
        $this->assertEquals(
            new Scalar\MagicConst\Class_,
            $unserializer->unserialize($xml)
        );
    }
    public function testScalars() {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<AST xmlns:scalar="http:
 <scalar:array>
  <scalar:array></scalar:array>
  <scalar:array/>
  <scalar:string>test</scalar:string>
  <scalar:string></scalar:string>
  <scalar:string/>
  <scalar:int>1</scalar:int>
  <scalar:float>1</scalar:float>
  <scalar:float>1.5</scalar:float>
  <scalar:true/>
  <scalar:false/>
  <scalar:null/>
 </scalar:array>
</AST>
XML;
        $result = array(
            array(), array(),
            'test', '', '',
            1,
            1, 1.5,
            true, false, null
        );
        $unserializer  = new XML;
        $this->assertEquals($result, $unserializer->unserialize($xml));
    }
    public function testWrongRootElementError() {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<notAST/>
XML;
        $unserializer = new XML;
        $unserializer->unserialize($xml);
    }
    public function testErrors($xml, $errorMsg) {
        $this->setExpectedException('DomainException', $errorMsg);
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<AST xmlns:scalar="http:
     xmlns:node="http:
     xmlns:subNode="http:
     xmlns:foo="http:
 $xml
</AST>
XML;
        $unserializer = new XML;
        $unserializer->unserialize($xml);
    }
    public function provideTestErrors() {
        return array(
            array('<scalar:true>test</scalar:true>',   '"true" scalar must be empty'),
            array('<scalar:false>test</scalar:false>', '"false" scalar must be empty'),
            array('<scalar:null>test</scalar:null>',   '"null" scalar must be empty'),
            array('<scalar:foo>bar</scalar:foo>',      'Unknown scalar type "foo"'),
            array('<scalar:int>x</scalar:int>',        '"x" is not a valid int'),
            array('<scalar:float>x</scalar:float>',    '"x" is not a valid float'),
            array('',                                  'Expected node or scalar'),
            array('<foo:bar>test</foo:bar>',           'Unexpected node of type "foo:bar"'),
            array(
                '<node:Scalar_String><foo:bar>test</foo:bar></node:Scalar_String>',
                'Expected sub node or attribute, got node of type "foo:bar"'
            ),
            array(
                '<node:Scalar_String><subNode:value/></node:Scalar_String>',
                'Expected node or scalar'
            ),
            array(
                '<node:Foo><subNode:value/></node:Foo>',
                'Unknown node type "Foo"'
            ),
        );
    }
}
