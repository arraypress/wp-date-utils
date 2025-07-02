# WordPress Date Utils

A comprehensive PHP library for WordPress date/time operations with UTC-first approach, timezone handling, ranges, subscriptions, and business logic.

## Features

- 🕐 **UTC-First Approach** - Safe database storage with local display conversion
- 🌍 **WordPress Timezone Integration** - Seamless wp_timezone() compatibility
- 📅 **Predefined Date Ranges** - Today, last week, this month, year-to-date, and more
- 💳 **Subscription Management** - Billing cycles, trials, grace periods, renewals
- ✅ **Date Validation** - Format checking, business days, age requirements
- 🏢 **Business Logic** - Business days, working hours, fiscal periods
- 🎨 **Smart Formatting** - WordPress formats, human-readable differences
- 🗄️ **Database Helpers** - Safe UTC storage and query building
- ⚡ **Fast Timestamps** - Quick calculations without Carbon overhead
- 🔧 **Carbon Integration** - Built on Carbon 2.x for reliability

## Installation

```bash
composer require arraypress/wp-date-utils
```

## Basic Usage

```php
use ArrayPress\DateUtils\Date;
use ArrayPress\DateUtils\Format;
use ArrayPress\DateUtils\Range;

// Get current times
$utc_now   = Date::now_utc();           // UTC for database
$local_now = Date::now_local();       // Local for display

// Convert between UTC and local
$utc_date     = Date::to_utc( $user_input );     // Store in database
$display_date = Date::to_local( $db_value ); // Show to user

// Get predefined ranges
$today      = Range::get( 'today' );              // Today's start/end in UTC
$last_month = Range::get( 'last_month' );    // Last month range

// Format for display
$formatted = Format::wp( $utc_date );        // WordPress format
$human     = Format::human( $utc_date );         // "2 hours ago"
```

## Advanced Usage

### Date Range Operations

```php
use ArrayPress\DateUtils\Range;

// All available ranges
$ranges = Range::options();
/* Returns:
[
    'today' => 'Today',
    'yesterday' => 'Yesterday', 
    'this_week' => 'This Week',
    'last_week' => 'Last Week',
    'this_month' => 'This Month',
    'last_month' => 'Last Month',
    'this_quarter' => 'This Quarter',
    'last_quarter' => 'Last Quarter',
    'this_year' => 'This Year',
    'last_year' => 'Last Year',
    'last_7_days' => 'Last 7 Days',
    'last_30_days' => 'Last 30 Days',
    'last_90_days' => 'Last 90 Days',
    'year_to_date' => 'Year to Date'
    // ... and more
]
*/

// Get specific range
$last_week = Range::get( 'last_week' );
// Returns: ['start' => '2025-06-16 00:00:00', 'end' => '2025-06-22 23:59:59']

// Convert user's local range to UTC for database queries
$utc_range = Range::local_to_utc( $start_local, $end_local );

// Get dates between two dates
$dates = Range::between( $start_utc, $end_utc, 'day' );
```

### Subscription Management

```php
<?php

use ArrayPress\DateUtils\Subscription;

// Get available billing periods
$periods = Subscription::get_periods();
/* Returns:
[
    'daily' => 'Daily',
    'weekly' => 'Weekly', 
    'monthly' => 'Monthly',
    'quarterly' => 'Quarterly',
    'biannual' => 'Biannual',
    'yearly' => 'Yearly'
]
*/

// Calculate renewal dates
$next_billing = Subscription::get_renewal_date( $start_utc, 'monthly' );
$trial_end    = Subscription::get_trial_end_date( $start_utc, 14 ); // 14-day trial

// Check subscription status
$status = Subscription::get_status( $expires_utc, 30 ); // 30-day grace period
/* Returns:
[
    'active' => true,
    'in_grace' => false,
    'expired' => false,
    'status' => 'active'
]
*/

// Check if subscription needs renewal
if ( Subscription::needs_renewal( $expires_utc, 7 ) ) {
	// Send renewal reminder
}
```

### Database Integration

```php
use ArrayPress\DateUtils\Database;

// Safe database operations
$utc_for_db  = Database::prepare( $user_input );     // Convert local to UTC
$for_display = Database::display( $db_value );      // Convert UTC to local

// Build database queries
$query_parts = Database::range_query( 'created_date', $start_local, $end_local );
$sql         = "SELECT * FROM posts WHERE {$query_parts['sql']}";
$results     = $wpdb->get_results( $wpdb->prepare( $sql, $query_parts['start_utc'], $query_parts['end_utc'] ) );

// WP_Query integration
$meta_query = Database::meta_query( 'event_date', $start_local, $end_local );
$posts      = new WP_Query( [ 'meta_query' => [ $meta_query ] ] );
```

### Validation & Business Logic

```php
use ArrayPress\DateUtils\Validate;

// Date validation
$is_valid        = Validate::is_valid_date( $date_string );
$is_future       = Validate::future( $utc_date );
$is_weekend      = Validate::weekend( $utc_date );
$is_business_day = Validate::business_day( $utc_date );

// Age validation
$meets_requirement = Validate::meets_age_requirement( $birth_date, 18 );

// Range validation
$in_range = Validate::range( $date, $start, $end );
```

### Fast Timestamp Operations

```php
use ArrayPress\DateUtils\Timestamp;

// Quick calculations (no Carbon overhead)
$expires    = Timestamp::in_hours( 2 );            // 2 hours from now
$cache_time = Timestamp::in_days( 1 );          // 1 day from now

// Age checking
$is_old = Timestamp::is_older_than( $timestamp, Timestamp::from_hours( 24 ) );

// Conversions
$hours   = Timestamp::to_hours( $seconds );
$seconds = Timestamp::from_minutes( 30 );
```

## Class Overview

| Class | Purpose | Key Methods |
|-------|---------|-------------|
| `Date` | Core operations | `now_utc()`, `to_utc()`, `to_local()`, `add()`, `subtract()` |
| `Format` | Display formatting | `wp()`, `human()`, `duration()`, `relative()` |
| `Range` | Date ranges | `get()`, `options()`, `between()`, `local_to_utc()` |
| `Validate` | Date validation | `is_valid_date()`, `future()`, `business_day()` |
| `Database` | DB integration | `prepare()`, `display()`, `range_query()` |
| `Subscription` | Billing logic | `get_periods()`, `get_renewal_date()`, `get_status()` |
| `Timestamp` | Fast operations | `in_hours()`, `is_older_than()`, `to_minutes()` |

## Common Patterns

### Date Picker Integration
```php
// User selects "today" in your UI
$local_range = Range::today_local();          // Get today in local timezone
$utc_range   = Range::local_to_utc( $local_range['start'], $local_range['end'] );

// Query database with UTC range
$results = $wpdb->get_results( $wpdb->prepare(
	"SELECT * FROM events WHERE event_date BETWEEN %s AND %s",
	$utc_range['start'],
	$utc_range['end']
) );

// Display results in user's timezone
foreach ( $results as $result ) {
	echo Database::display( $result->event_date );
}
```

### Admin Column with Relative Time
```php
public function column_modified( array $item ): string {
	$utc_date  = $item['modified_date'];
	$formatted = Format::wp( $utc_date, 'datetime' );
	$relative  = Format::human( $utc_date );

	return $formatted . '<br><small class="description">' . $relative . '</small>';
}
```

## Requirements

- PHP 7.4 or higher
- WordPress 5.3 or higher (for `wp_timezone()`)
- Carbon 2.73 or higher

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL-2.0-or-later License.

## Support

- [Documentation](https://github.com/arraypress/wp-date-utils)
- [Issue Tracker](https://github.com/arraypress/wp-date-utils/issues)