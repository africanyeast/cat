<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\ValueObject\TimePeriod;
use InvalidArgumentException;

class TimePeriodTest extends TestCase
{
    public function testConstructWithValidMonthFormat(): void
    {
        $period = new TimePeriod('2024-03');
        
        $this->assertEquals('2024-03-01', $period->getStart()->format('Y-m-d'));
        $this->assertEquals('2024-03-31', $period->getEnd()->format('Y-m-d'));
    }

    public function testConstructWithValidDaysFormat(): void
    {
        // Freeze time for consistent testing
        $mockToday = new \DateTimeImmutable('2024-03-15');
        $expectedStart = '2024-02-14'; // 30 days before 2024-03-15
        $expectedEnd = '2024-03-15';
        
        $period = new TimePeriod('30d', $mockToday);
        
        $this->assertEquals($expectedStart, $period->getStart()->format('Y-m-d'));
        $this->assertEquals($expectedEnd, $period->getEnd()->format('Y-m-d'));
    }

    public function testConstructWithFebruaryMonth(): void
    {
        $period = new TimePeriod('2024-02');
        
        $this->assertEquals('2024-02-01', $period->getStart()->format('Y-m-d'));
        $this->assertEquals('2024-02-29', $period->getEnd()->format('Y-m-d')); // 2024 is leap year
    }

    public function testConstructWithInvalidFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid period format: 'invalid'");
        
        new TimePeriod('invalid');
    }

    public function testConstructWithInvalidMonthFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid period format: '2024-13'");
        
        new TimePeriod('2024-13');
    }

    public function testConstructWithInvalidDaysFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid period format: '0d'");
        
        new TimePeriod('0d');
    }
}