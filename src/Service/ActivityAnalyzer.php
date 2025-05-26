<?php

namespace App\Service;

class ActivityAnalyzer
{
    private array $games = [];

    public function addGames(array $games): void
    {
        $this->games = array_merge($this->games, $games);
    }
    public function analyze(): array
    {
        return [
            'summary' => $this->analyzeSummary(),
            'timeControl' => $this->analyzeTimeControl(),
            'patterns' => $this->analyzePatterns(),
        ];
    }

    private function analyzeSummary(): array
    {
        $totalGames = count($this->games);
        $dailyCounts = [];

        foreach ($this->games as $game) {
            $ts = $game['end_time'] ?? null;
            if (!$ts) continue;

            $day = date('Y-m-d', $ts);
            $dailyCounts[$day] = ($dailyCounts[$day] ?? 0) + 1;
        }

        $activeDays = count($dailyCounts);
        $avg = $activeDays ? round($totalGames / $activeDays, 1) : 0;

        arsort($dailyCounts);
        // $peakDay = array_key_first($dailyCounts) ?: '';
        $peakCount = reset($dailyCounts) ?: 0;

        return [
            'total_games' => $totalGames,
            'active_days' => $activeDays,
            'average_per_day' => $avg,
            // 'most_active_day' => $peakDay,
            'peak_games' => $peakCount,
        ];
    }

    private function analyzeTimeControl(): array
    {
        $timeControls = [];

        foreach ($this->games as $game) {
            $control = $game['time_control'] ?? 'unknown';
            $timeControls[$control] = ($timeControls[$control] ?? 0) + 1;
        }

        $totalGames = count($this->games);
        $activeDays = $this->countActiveDays();

        $result = [];
        foreach ($timeControls as $format => $count) {
            $avgPerDay = $activeDays ? round($count / $activeDays, 1) : 0;
            $percent = $totalGames ? round(($count / $totalGames) * 100, 1) . '%' : '0%';

            $result[] = [
                'format' => $format,
                'games' => $count,
                'avg_per_day' => $avgPerDay,
                'percent' => $percent,
            ];
        }

        return $result;
    }

    private function countActiveDays(): int
    {
        $days = [];

        foreach ($this->games as $game) {
            $ts = $game['end_time'] ?? null;
            if (!$ts) continue;

            $day = date('Y-m-d', $ts);
            $days[$day] = true;
        }

        return count($days);
    }

    private function analyzePatterns(): array
    {

        $dailyCounts = [];
        $hourlyCounts = [];
        $timestamps = [];

        foreach ($this->games as $game) {
            $ts = $game['end_time'] ?? null;
            if (!$ts) continue;

            $date = date('Y-m-d', $ts);
            $hour = (int) date('H', $ts);

            $dailyCounts[$date] = ($dailyCounts[$date] ?? 0) + 1;
            $hourlyCounts[$hour] = ($hourlyCounts[$hour] ?? 0) + 1;

            $timestamps[] = $ts;
        }

        // Sort timestamps ascending
        sort($timestamps);

        // Most active day
        arsort($dailyCounts);
        $mostActiveDay = array_key_first($dailyCounts);

        // Most active hour
        arsort($hourlyCounts);
        $mostActiveHour = array_key_first($hourlyCounts);

        // Calculate longest streak and longest break
        [$longestStreak, $longestBreak] = $this->calculateStreaks(array_keys($dailyCounts));

        // Get last active date
         $lastTs = end($timestamps);
        // Format last active date
        $lastActiveDate = date('Y-m-d', $lastTs);

        return [
            'most_active_day' => $mostActiveDay,
            'most_active_hour' => sprintf('%02d:00 - %02d:00', $mostActiveHour, ($mostActiveHour + 1) % 24),
            'longest_streak' => $longestStreak . ' days',
            'longest_break' => $longestBreak . ' days',
            'last_active' => $lastActiveDate,
        ];
    }

    /*
     * Calculate longest consecutive streak and longest break in days from array of 'Y-m-d' dates
     */
    private function calculateStreaks(array $dates): array
    {
        if (empty($dates)) {
            return [0, 0];
        }

        sort($dates);

        $longestStreak = 1;
        $currentStreak = 1;
        $longestBreak = 0;

        for ($i = 1, $len = count($dates); $i < $len; $i++) {
            $prevDate = new \DateTimeImmutable($dates[$i - 1]);
            $currDate = new \DateTimeImmutable($dates[$i]);
            $diffDays = (int) $currDate->diff($prevDate)->format('%a');

            if ($diffDays === 1) {
                // Consecutive day - increment streak
                $currentStreak++;
            } else {
                // Break detected
                if ($currentStreak > $longestStreak) {
                    $longestStreak = $currentStreak;
                }
                if ($diffDays > $longestBreak) {
                    $longestBreak = $diffDays - 1; // days without activity between streaks
                }
                $currentStreak = 1;
            }
        }

        // Check last streak
        if ($currentStreak > $longestStreak) {
            $longestStreak = $currentStreak;
        }

        return [$longestStreak, $longestBreak];
    }

}
