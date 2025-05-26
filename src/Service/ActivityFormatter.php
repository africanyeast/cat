<?php

namespace App\Service;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class ActivityFormatter
{
    public function formatSummary(array $metrics, OutputInterface $output): void
    {
        $output->writeln('');
        $output->writeln('<info>Activity Overview:</info>');

        $table = new Table($output);
        $table
            ->setHeaders(['Metric', 'Value'])
            ->setRows([
                ['Total Games', $metrics['total_games']],
                ['Active Days', $metrics['active_days']],
                ['Avg Games/Day', $metrics['average_per_day']],
                // ['Most Active Day', $metrics['most_active_day']],
                ['Games (Peak)', $metrics['peak_games']],
            ])
            ->render();

        $output->writeln('');
    }
    public function formatTimeControl(array $data, OutputInterface $output): void
    {
        $output->writeln('<info>Time Control Breakdown:</info>');

        $table = new Table($output);
        $table
            ->setHeaders(['Format', 'Games', 'Avg/Day', '% of Total'])
            ->setRows(array_map(fn ($row) => [
                $row['format'], $row['games'], $row['avg_per_day'], $row['percent']
            ], $data))
            ->render();

        $output->writeln('');
    }

    public function formatPatterns(array $data, OutputInterface $output): void
    {
        $output->writeln('<info>Activity Patterns:</info>');

        $table = new Table($output);
        $table
            ->setHeaders(['Metric', 'Value'])
            ->setRows([
                ['Most Active Day', $data['most_active_day']],
                ['Most Active Hour', $data['most_active_hour']],
                ['Longest Streak', $data['longest_streak']],
                ['Longest Break', $data['longest_break']],
                ['Last Active', $data['last_active']],
            ])
            ->render();

        $output->writeln('');
    }

}
