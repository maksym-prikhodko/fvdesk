<?php
namespace integration\PhpSpec\Console\Prompter;
use PhpSpec\Console\Prompter\Dialog;
class DialogTest extends \PHPUnit_Framework_TestCase
{
    private $output;
    private $dialogHelper;
    private $prompter;
    protected function setUp()
    {
        $this->output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $this->dialogHelper = $this->getMockBuilder('Symfony\Component\Console\Helper\DialogHelper')
                                   ->disableOriginalConstructor()->getMock();
        $this->prompter = new Dialog($this->output, $this->dialogHelper);
    }
    function it_is_a_prompter()
    {
        $this->assertInstanceOf('PhpSpec\Console\Prompter', $this->prompter);
    }
    function it_can_ask_a_question_and_return_the_result()
    {
        $this->dialogHelper->expects($this->once())
                           ->method('askConfirmation')
                           ->with($this->identicalTo($this->output), 'Are you sure?', true)
                           ->willReturn(true);
        $result = $this->prompter->askConfirmation('Are you sure?');
        $this->assertEquals(true, $result);
    }
}
