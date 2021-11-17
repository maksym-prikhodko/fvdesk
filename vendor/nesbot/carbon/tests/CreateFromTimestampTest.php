<?php
use Carbon\Carbon;
class CreateFromTimestampTest extends TestFixture
{
    public function testCreateReturnsDatingInstance()
    {
        $d = Carbon::createFromTimestamp(Carbon::create(1975, 5, 21, 22, 32, 5)->timestamp);
        $this->assertCarbon($d, 1975, 5, 21, 22, 32, 5);
    }
    public function testCreateFromTimestampUsesDefaultTimezone()
    {
        $d = Carbon::createFromTimestamp(0);
        $this->assertSame(1969, $d->year);
        $this->assertSame(-5 * 3600, $d->offset);
    }
    public function testCreateFromTimestampWithDateTimeZone()
    {
        $d = Carbon::createFromTimestamp(0, new \DateTimeZone('UTC'));
        $this->assertSame('UTC', $d->tzName);
        $this->assertCarbon($d, 1970, 1, 1, 0, 0, 0);
    }
    public function testCreateFromTimestampWithString()
    {
        $d = Carbon::createFromTimestamp(0, 'UTC');
        $this->assertCarbon($d, 1970, 1, 1, 0, 0, 0);
        $this->assertSame(0, $d->offset);
        $this->assertSame('UTC', $d->tzName);
    }
    public function testCreateFromTimestampGMTDoesNotUseDefaultTimezone()
    {
        $d = Carbon::createFromTimestampUTC(0);
        $this->assertCarbon($d, 1970, 1, 1, 0, 0, 0);
        $this->assertSame(0, $d->offset);
    }
}
