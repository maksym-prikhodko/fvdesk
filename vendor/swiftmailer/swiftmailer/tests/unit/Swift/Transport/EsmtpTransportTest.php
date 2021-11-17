<?php
class Swift_Transport_EsmtpTransportTest
    extends Swift_Transport_AbstractSmtpEventSupportTest
{
    protected function _getTransport($buf, $dispatcher = null)
    {
        if (!$dispatcher) {
            $dispatcher = $this->_createEventDispatcher();
        }
        return new Swift_Transport_EsmtpTransport($buf, array(), $dispatcher);
    }
    public function testHostCanBeSetAndFetched()
    {
        $buf = $this->_getBuffer();
        $smtp = $this->_getTransport($buf);
        $smtp->setHost('foo');
        $this->assertEquals('foo', $smtp->getHost(), '%s: Host should be returned');
    }
    public function testPortCanBeSetAndFetched()
    {
        $buf = $this->_getBuffer();
        $smtp = $this->_getTransport($buf);
        $smtp->setPort(25);
        $this->assertEquals(25, $smtp->getPort(), '%s: Port should be returned');
    }
    public function testTimeoutCanBeSetAndFetched()
    {
        $buf = $this->_getBuffer();
        $buf->shouldReceive('setParam')
            ->once()
            ->with('timeout', 10);
        $smtp = $this->_getTransport($buf);
        $smtp->setTimeout(10);
        $this->assertEquals(10, $smtp->getTimeout(), '%s: Timeout should be returned');
    }
    public function testEncryptionCanBeSetAndFetched()
    {
        $buf = $this->_getBuffer();
        $smtp = $this->_getTransport($buf);
        $smtp->setEncryption('tls');
        $this->assertEquals('tls', $smtp->getEncryption(), '%s: Crypto should be returned');
    }
    public function testStartSendsHeloToInitiate()
    {
    }
    public function testStartSendsEhloToInitiate()
    {
        $buf = $this->_getBuffer();
        $smtp = $this->_getTransport($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('readLine')
            ->once()
            ->with(0)
            ->andReturn("220 some.server.tld bleh\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with('~^EHLO .+?\r\n$~D')
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('250 ServerName'."\r\n");
        $this->_finishBuffer($buf);
        try {
            $smtp->start();
        } catch (Exception $e) {
            $this->fail('Starting Esmtp should send EHLO and accept 250 response');
        }
    }
    public function testHeloIsUsedAsFallback()
    {
        $buf = $this->_getBuffer();
        $smtp = $this->_getTransport($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('readLine')
            ->once()
            ->with(0)
            ->andReturn("220 some.server.tld bleh\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with('~^EHLO .+?\r\n$~D')
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('501 WTF'."\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with('~^HELO .+?\r\n$~D')
            ->andReturn(2);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(2)
            ->andReturn('250 HELO'."\r\n");
        $this->_finishBuffer($buf);
        try {
            $smtp->start();
        } catch (Exception $e) {
            $this->fail(
                'Starting Esmtp should fallback to HELO if needed and accept 250 response'
                );
        }
    }
    public function testInvalidHeloResponseCausesException()
    {
        $buf = $this->_getBuffer();
        $smtp = $this->_getTransport($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('readLine')
            ->once()
            ->with(0)
            ->andReturn("220 some.server.tld bleh\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with('~^EHLO .+?\r\n$~D')
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('501 WTF'."\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with('~^HELO .+?\r\n$~D')
            ->andReturn(2);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(2)
            ->andReturn('504 WTF'."\r\n");
        $this->_finishBuffer($buf);
        try {
            $this->assertFalse($smtp->isStarted(), '%s: SMTP should begin non-started');
            $smtp->start();
            $this->fail('Non 250 HELO response should raise Exception');
        } catch (Exception $e) {
            $this->assertFalse($smtp->isStarted(), '%s: SMTP start() should have failed');
        }
    }
    public function testDomainNameIsPlacedInEhlo()
    {
        $buf = $this->_getBuffer();
        $smtp = $this->_getTransport($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('readLine')
            ->once()
            ->with(0)
            ->andReturn("220 some.server.tld bleh\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("EHLO mydomain.com\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('250 ServerName'."\r\n");
        $this->_finishBuffer($buf);
        $smtp->setLocalDomain('mydomain.com');
        $smtp->start();
    }
    public function testDomainNameIsPlacedInHelo()
    {
        $buf = $this->_getBuffer();
        $smtp = $this->_getTransport($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('readLine')
            ->once()
            ->with(0)
            ->andReturn("220 some.server.tld bleh\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with('~^EHLO .+?\r\n$~D')
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('501 WTF'."\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("HELO mydomain.com\r\n")
            ->andReturn(2);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(2)
            ->andReturn('250 ServerName'."\r\n");
        $this->_finishBuffer($buf);
        $smtp->setLocalDomain('mydomain.com');
        $smtp->start();
    }
    public function testFluidInterface()
    {
        $buf = $this->_getBuffer();
        $smtp = $this->_getTransport($buf);
        $buf->shouldReceive('setParam')
            ->once()
            ->with('timeout', 30);
        $ref = $smtp
            ->setHost('foo')
            ->setPort(25)
            ->setEncryption('tls')
            ->setTimeout(30)
            ;
        $this->assertEquals($ref, $smtp);
    }
}
