<?php
namespace PhpParser;
class CommentTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSet() {
        $comment = new Comment('', 1);
        $this->assertSame('', $comment->getText());
        $this->assertSame('', (string) $comment);
        $this->assertSame(1, $comment->getLine());
        $comment->setText('');
        $comment->setLine(10);
        $this->assertSame('', $comment->getText());
        $this->assertSame('', (string) $comment);
        $this->assertSame(10, $comment->getLine());
    }
    public function testReformatting($commentText, $reformattedText) {
        $comment = new Comment($commentText);
        $this->assertSame($reformattedText, $comment->getReformattedText());
    }
    public function provideTestReformatting() {
        return array(
            array('
            array('', ''),
            array(
                '',
                ''
            ),
            array(
                '',
                ''
            ),
            array(
                '',
                ''
            ),
            array(
                'hallo
    world',
                'hallo
    world',
            ),
        );
    }
}
