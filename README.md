# Chess.com Activity Tracker (CAT)
*A Symfony CLI Tool for User Activity Analysis*

## MVP Objective

**Primary Goal**: Learn Symfony framework fundamentals by building a practical CLI application that analyzes Chess.com user activity patterns over specified time periods.

**Learning Focus**: 
- Symfony Console Component proficiency
- HTTP client integration and API consumption
- Service container and dependency injection
- Testing with PHPUnit in Symfony context

## Core Technical Challenge

Build a focused CLI tool that fetches and analyzes Chess.com user activity data using the Symfony PHP framework. This entails:

- Creating a command-line interface with Symfony Console
- Implementing service-oriented architecture with dependency injection
- Consuming external APIs (Chess.com) with proper error handling
- Analyzing and formatting array data for user-friendly output

## Feature Set: User Activity Analysis

### Primary Command: `cat:activity [username] [period]`

**Activity Metrics Tracked:**
- **Game Activity**: Games played per day/week in specified period
- **Time Control Distribution**: Breakdown of bullet/blitz/rapid/daily games
- **Playing Patterns**: Most active days of week and hours
- **Streak Analysis**: Longest active/inactive periods
- **Platform Engagement**: Last active

### Sample Usage

```bash
# Analyze last 30 days
php bin/console cat:activity hikaru --period=30d

# Analyze specific month  
php bin/console cat:activity hikaru --period=2025-05
```
Note: The Chess.com API currently only returns all games for a given month (e.g., May 2024), and does not support fetching games for arbitrary periods such as 15d or 25d. As a result, period formats like 30d, 60d, 90d, etc., are the most reliable for now. If you specify a period like 15d , the tool will fetch all games for the relevant month(s) and may include extra games outside the requested range. In the future, we may refactor the tool to filter games by date before passing them to the analyzer for more precise period handling.

### Expected Output

```
Chess.com Activity Analysis: Hikaru (Last 30 days)
=================================================

Activity Overview:
┌─────────────────┬─────────────┐
│ Total Games     │ 342         │
│ Active Days     │ 28          │
│ Avg Games/Day   │ 11.4        │
│ Games (Peak)    │ 24          │
└─────────────────┴─────────────┘

Time Control Breakdown:
┌─────────┬───────┬──────────┬─────────────┐
│ Format  │ Games │ Avg/Day  │ % of Total  │
├─────────┼───────┼──────────┼─────────────┤
│ Bullet  │  189  │   6.3    │    55.3%    │
│ Blitz   │  127  │   4.2    │    37.1%    │
│ Rapid   │   26  │   0.9    │     7.6%    │
└─────────┴───────┴──────────┴─────────────┘

Activity Patterns:
┌────────────────────┬──────────────────────────┐
│ Most Active Day    │ 2025-05-13               │
│ Most Active Hour   │ 16:00 - 17:00            │
│ Longest Streak     │ 12 days                  │
│ Longest Break      │ 2 days                   │
│ Last Active        │ 2025-05-23               │
└────────────────────┴──────────────────────────┘
```

## Technical Architecture

### Symfony Components

- **Console Component**: Command structure and I/O handling
- **HttpClient Component**: Chess.com API integration  

### Chess.com API Endpoints

```
GET /pub/player/{username}                    # User profile
GET /pub/player/{username}/games/{YYYY}/{MM}  # Monthly games
```

### Service Architecture

```php
// Commands
src/Command/ActivityAnalysisCommand.php

// Services
src/Service/ChessComApiClient.php      # API integration
src/Service/ActivityAnalyzer.php       # Core analysis logic
src/Service/ActivityFormatter.php      # Output formatting

// Value Object
src/ValueObject/TimePeriod.php
```

### Unit Tests

**Framework**: PHPUnit with Symfony Test Integration

```php
// Service Tests
tests/Service/ActivityAnalyzerTest.php  # Tests for analysis logic

// Value Object Tests
tests/Service/TimePeriodTest.php        # Period parsing/validation

// Additional tests can be added for:
// - ChessComApiClient service
// - ActivityFormatter service
```

## Installation & Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- Git

### Quick Start

```bash
# Clone the repository
git clone https://github.com/africanyeast/cat.git
cd cat

# Install dependencies
composer install

# Verify installation
php bin/console list

# Run the activity analysis
php bin/console cat:activity hikaru --period=30d
```

### Development Setup

```bash
# Install dev dependencies
composer install --dev

# Run tests
vendor/bin/phpunit
```

## Implementation Roadmap

### Phase 1: Foundation (1 hour)
- [✓] Symfony project setup with Console component
- [✓] Basic command structure and argument parsing
- [✓] Chess.com API client with HTTP client
- [✓] Simple user profile fetching

### Phase 2: Core Functionality (2 hours)
- [✓] Time period parsing and validation
- [✓] Game data fetching and processing
- [✓] Activity metrics calculation

### Phase 3: Testing & Output Formatting (0.5 hour)
- [✓] Unit testing for analyzer and time period outputs
- [✓] Console table formatting for activity metrics