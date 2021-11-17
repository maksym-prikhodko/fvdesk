<?php
namespace Monolog\Handler;
use Monolog\TestCase;
class NativeMailerHandlerTest extends TestCase
{
    public function testConstructorHeaderInjection()
    {
        $mailer = new NativeMailerHandler('spammer@example.org', 'dear victim', "receiver@example.org\r\nFrom: faked@attacker.org");
    }
    public function testSetterHeaderInjection()
    {
        $mailer = new NativeMailerHandler('spammer@example.org', 'dear victim', 'receiver@example.org');
        $mailer->addHeader("Content-Type: text/html\r\nFrom: faked@attacker.org");
    }
    public function testSetterArrayHeaderInjection()
    {
        $mailer = new NativeMailerHandler('spammer@example.org', 'dear victim', 'receiver@example.org');
        $mailer->addHeader(array("Content-Type: text/html\r\nFrom: faked@attacker.org"));
    }
    public function testSetterContentTypeInjection()
    {
        $mailer = new NativeMailerHandler('spammer@example.org', 'dear victim', 'receiver@example.org');
        $mailer->setContentType("text/html\r\nFrom: faked@attacker.org");
    }
    public function testSetterEncodingInjection()
    {
        $mailer = new NativeMailerHandler('spammer@example.org', 'dear victim', 'receiver@example.org');
        $mailer->setEncoding("utf-8\r\nFrom: faked@attacker.org");
    }
}
