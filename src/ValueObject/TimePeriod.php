<?php

namespace App\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

class TimePeriod
{
    private DateTimeImmutable $start;
    private DateTimeImmutable $end;

    public function __construct(string $input, ?DateTimeImmutable $currentDate = null)
    {
        if (preg_match('/^\d{4}-\d{2}$/', $input)) {
            // Format: YYYY-MM
            try {
                $this->start = new DateTimeImmutable($input . '-01');
                $this->end = $this->start->modify('last day of this month');
            } catch (\Exception $e) {
                throw new InvalidArgumentException("Invalid period format: '$input'");
            }
        } elseif (preg_match('/^\d+d$/', $input)) {
            // Format: 30d, 7d
            $days = (int) rtrim($input, 'd');
            if ($days <= 0) {
                throw new InvalidArgumentException("Invalid period format: '$input'");
            }
            $this->end = $currentDate ?? new DateTimeImmutable('today');
            $this->start = $this->end->modify("-$days days");
        } else {
            throw new InvalidArgumentException("Invalid period format: '$input'");
        }
    }

    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    public function getEnd(): DateTimeImmutable
    {
        return $this->end;
    }
}
