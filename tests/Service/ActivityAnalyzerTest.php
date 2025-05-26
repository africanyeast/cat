<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\ActivityAnalyzer;

class ActivityAnalyzerTest extends TestCase
{
    private ActivityAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new ActivityAnalyzer();
    }

    public function testAnalyzeWithNoGames(): void
    {
        $this->analyzer->addGames([]); // Add empty games array
        $metrics = $this->analyzer->analyze();

        // Test summary section
        $this->assertEquals(0, $metrics['summary']['total_games']);
        $this->assertEquals(0, $metrics['summary']['active_days']);
        $this->assertEquals('', $metrics['summary']['most_active_day']);
        $this->assertEquals(0, $metrics['summary']['peak_games']);
        
        // Test timeControl section
        $this->assertIsArray($metrics['timeControl']);
        $this->assertEmpty($metrics['timeControl']);
        
        // Test patterns section
        $this->assertIsArray($metrics['patterns']);
        $this->assertEquals('0 days', $metrics['patterns']['longest_streak']);
        $this->assertEquals('0 days', $metrics['patterns']['longest_break']);
    }

    public function testAnalyzeWithDummyGames(): void
    {
        // Sample games array with different time controls
        $games = [
            ['end_time' => strtotime('2024-05-01 14:00'), 'time_control' => 'blitz'],
            ['end_time' => strtotime('2024-05-01 15:00'), 'time_control' => 'blitz'],
            ['end_time' => strtotime('2024-05-02 10:00'), 'time_control' => 'rapid'],
        ];

        $this->analyzer->addGames($games);
        $metrics = $this->analyzer->analyze();

        // Test summary section
        $this->assertEquals(3, $metrics['summary']['total_games']);
        $this->assertEquals(2, $metrics['summary']['active_days']);
        $this->assertEquals(1.5, $metrics['summary']['average_per_day']);
        $this->assertEquals(2, $metrics['summary']['peak_games']);
        
        // Test timeControl section
        $this->assertIsArray($metrics['timeControl']);
        $this->assertCount(2, $metrics['timeControl']); // blitz and rapid
        
        // Find blitz format in results
        $blitzFormat = null;
        foreach ($metrics['timeControl'] as $format) {
            if ($format['format'] === 'blitz') {
                $blitzFormat = $format;
                break;
            }
        }
        
        $this->assertNotNull($blitzFormat, 'Blitz format should be present in timeControl');
        $this->assertEquals(2, $blitzFormat['games']);
        $this->assertEquals(1, $blitzFormat['avg_per_day']);
        $this->assertEquals('66.7%', $blitzFormat['percent']);
        
        // Test patterns section
        $this->assertIsArray($metrics['patterns']);
        $this->assertEquals('2024-05-01', $metrics['patterns']['most_active_day']);
        $this->assertStringContainsString('days', $metrics['patterns']['longest_streak']);
        $this->assertEquals('2 days', $metrics['patterns']['longest_streak']);
        $this->assertEquals('0 days', $metrics['patterns']['longest_break']);
        $this->assertEquals('2024-05-02', $metrics['patterns']['last_active']);
    }
}

