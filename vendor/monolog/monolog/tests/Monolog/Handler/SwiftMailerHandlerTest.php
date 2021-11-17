<?php
namespace Monolog\Handler;
use Monolog\Logger;
use Monolog\TestCase;
class SwiftMailerHandlerTest extends TestCase
{
    private $mailer;
    public function setUp()
    {
        $this->mailer = $this
            ->getMockBuilder('Swift_Mailer')
            ->disableOriginalConstructor()
            ->getMock();
    }
    public function testMessageCreationIsLazyWhenUsingCallback()
    {
        $this->mailer->expects($this->never())
            ->method('send');
        $callback = function () {
            throw new \RuntimeException('Swift_Message creation callback should not have been called in this test');
        };
        $handler = new SwiftMailerHandler($this->mailer, $callback);
        $records = array(
            $this->getRecord(Logger::DEBUG),
            $this->getRecord(Logger::INFO),
        );
        $handler->handleBatch($records);
    }
    public function testMessageCanBeCustomizedGivenLoggedData()
    {
        $expectedMessage = new \Swift_Message();
        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($value) use ($expectedMessage) {
                return $value instanceof \Swift_Message
                    && $value->getSubject() === 'Emergency'
                    && $value === $expectedMessage;
            }));
        $callback = function ($content, array $records) use ($expectedMessage) {
            $subject = count($records) > 0 ? 'Emergency' : 'Normal';
            $expectedMessage->setSubject($subject);
            return $expectedMessage;
        };
        $handler = new SwiftMailerHandler($this->mailer, $callback);
        $records = array(
            $this->getRecord(Logger::EMERGENCY),
        );
        $handler->handleBatch($records);
    }
}
