<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// Custom classes
use App\ValueObject\TimePeriod;
use App\Service\ActivityAnalyzer;
use App\Service\ActivityFormatter;
use App\Service\ChessComApiClient;

#[AsCommand(
    name: 'cat:activity',
    description: 'Command that returns chess.com player actvities given a username and period.'
)]
class ActivityAnalysisCommand extends Command
{
    private ChessComApiClient $apiClient;
    private ActivityAnalyzer $analyzer;
    private ActivityFormatter $formatter;
    public function __construct(
        ChessComApiClient $apiClient,
        ActivityAnalyzer $analyzer,
        ActivityFormatter $formatter
    ){
        parent::__construct();
        $this->apiClient = $apiClient;
        $this->analyzer = $analyzer;
        $this->formatter = $formatter;
    }

    protected function configure():void
    {
        $this->addArgument('username', InputArgument::REQUIRED, 'The Chess.com username');
        $this->addOption('period', null, InputOption::VALUE_REQUIRED, 'Time period to analyze, e.g. 30d or 2024-03');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $period = $this->parsePeriod($input->getOption('period'));

        $profile = $this->apiClient->fetchUserProfile($username);
        if (!$profile) {
            $output->writeln("<error>User '$username' not found or API error occurred.</error>");
            return Command::FAILURE;
        }

        $gamesFound = false;

        foreach ($this->fetchGamesForPeriod($username, $period->getStart(), $period->getEnd()) as $monthlyGames) {
            if (!empty($monthlyGames)) {
                $gamesFound = true;
                $this->analyzer->addGames($monthlyGames);
            }
        }
        
        if (!$gamesFound) {
            $output->writeln("<comment>No games found for user '$username' in the specified period.</comment>");
            return Command::SUCCESS;
        }

        $metrics = $this->analyzer->analyze();
        $this->formatAndDisplayMetrics($metrics, $output, $username, $period);

        return Command::SUCCESS;
    }

    private function parsePeriod(?string $period): TimePeriod
    {
        if (null === $period) {
            // Default to last 30 days
            return new TimePeriod('30d');
        }
        return new TimePeriod($period);
    }


    private function formatAndDisplayMetrics(array $metrics, OutputInterface $output, string $username, TimePeriod $period): void
    {
        // Add header with username and period description
        $periodDescription = $this->getPeriodDescription($period);
        $username = ucfirst($username);
        $header = "Chess.com Activity Analysis: $username ($periodDescription)";
        $separator = str_repeat('=', strlen($header));
        
        $output->writeln([
            '',
            "<info>$header</info>",
            "<info>$separator</info>",
            ''
        ]);
        
        $this->formatter->formatSummary($metrics['summary'], $output);
        $this->formatter->formatTimeControl($metrics['timeControl'], $output);
        $this->formatter->formatPatterns($metrics['patterns'], $output);
    }

    private function getPeriodDescription(TimePeriod $period): string
    {
        $start = $period->getStart();
        $end = $period->getEnd();
        
        // If period is approximately 30 days
        $diff = $start->diff($end)->days;
        if ($diff >= 28 && $diff <= 31) {
            return "Last 30 days";
        }
        
        // If period is a specific month
        if ($start->format('d') === '01' && $end->format('d') === $end->format('t')) {
            return $start->format('F Y');
        }
        
        // Default: show date range
        return $start->format('Y-m-d') . ' - ' . $end->format('Y-m-d');
    }

    private function fetchGamesForPeriod(string $username, \DateTimeImmutable $start, \DateTimeImmutable $end): \Generator
    {
        $cursor = $start;

        while ($cursor <= $end) {
            $year = $cursor->format('Y');
            $month = $cursor->format('m');
            $monthlyGames = $this->apiClient->fetchMonthlyGames($username, $year, $month);
            yield $monthlyGames;
            $cursor = $cursor->modify('+1 month');
        }
    }
}
