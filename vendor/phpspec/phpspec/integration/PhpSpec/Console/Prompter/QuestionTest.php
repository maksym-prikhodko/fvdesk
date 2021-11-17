<?php
namespace integration\PhpSpec\Console\Prompter;
use PhpSpec\Console\Prompter\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
class QuestionTest extends \PHPUnit_Framework_TestCase
{
    private $input;
    private $output;
    private $questionHelper;
    private $prompter;
    protected function setUp()
    {
        $this->input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $this->output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $this->questionHelper = $this->getMock('Symfony\Component\Console\Helper\QuestionHelper');
        $this->prompter = new Question($this->input, $this->output, $this->questionHelper);
    }
    function it_is_a_prompter()
    {
        $this->assertInstanceOf('PhpSpec\Console\Prompter', $this->prompter);
    }
    function it_can_ask_a_question_and_return_the_result()
    {
        $this->questionHelper->expects($this->once())
                           ->method('ask')
                           ->with(
                               $this->identicalTo($this->input),
                               $this->identicalTo($this->output),
                               $this->equalTo(new ConfirmationQuestion('Are you sure?', true))
                           )
                           ->willReturn(true);
        $result = $this->prompter->askConfirmation('Are you sure?');
        $this->assertEquals(true, $result);
    }
}
