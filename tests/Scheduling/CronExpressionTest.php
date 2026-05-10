<?php

namespace Tests\Scheduling;

use PHPUnit\Framework\TestCase;
use Smerteliko\MicroCli\Scheduling\CronExpression;
use DateTimeImmutable;
use InvalidArgumentException;

class CronExpressionTest extends TestCase
{
    public function testAsteriskMatchesAlways(): void
    {
        $cron = new CronExpression('* * * * *');
        $date = new DateTimeImmutable('2023-10-15 14:30:00');

        $this->assertTrue($cron->isDue($date));
    }

    public function testStepMatchesProperly(): void
    {
        // Every 5 minutes
        $cron = new CronExpression('*/5 * * * *');

        $this->assertTrue($cron->isDue(new DateTimeImmutable('14:05:00')));
        $this->assertTrue($cron->isDue(new DateTimeImmutable('14:30:00')));
        $this->assertFalse($cron->isDue(new DateTimeImmutable('14:07:00')));
    }

    public function testListMatchesProperly(): void
    {
        // At minute 0, 15, and 30
        $cron = new CronExpression('0,15,30 * * * *');

        $this->assertTrue($cron->isDue(new DateTimeImmutable('14:15:00')));
        $this->assertTrue($cron->isDue(new DateTimeImmutable('14:30:00')));
        $this->assertFalse($cron->isDue(new DateTimeImmutable('14:45:00')));
    }

    public function testRangeMatchesProperly(): void
    {
        // Between hours 9 and 17
        $cron = new CronExpression('* 9-17 * * *');

        $this->assertTrue($cron->isDue(new DateTimeImmutable('10:30:00')));
        $this->assertTrue($cron->isDue(new DateTimeImmutable('17:59:00')));
        $this->assertFalse($cron->isDue(new DateTimeImmutable('08:30:00')));
        $this->assertFalse($cron->isDue(new DateTimeImmutable('18:00:00')));
    }

    public function testInvalidExpressionThrowsException(): void
    {
        $cron = new CronExpression('* * *'); // Only 3 parts

        $this->expectException(InvalidArgumentException::class);
        $cron->isDue();
    }
}