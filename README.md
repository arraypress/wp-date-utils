# WordPress Date Utilities

Lightweight date utilities for WordPress focusing on UTC storage and local display without external dependencies.

## Features

- **Zero Dependencies** - Uses only WordPress core and PHP built-ins
- **UTC Storage** - Store dates safely in UTC for database
- **Local Display** - Convert to site timezone for display
- **Date Ranges** - 12 essential predefined ranges (today, last week, last 90 days, etc.)
- **Date Math** - Add/subtract days, hours, months with precision
- **Business Logic** - Weekend/business day checking, age calculation
- **Subscription Periods** - Handle billing cycles with Stripe compatibility
- **Formatting** - WordPress date/time settings with i18n support
- **Validation** - Date format checking, expiration logic, freshness checks
- **Database Helpers** - Query builders for date ranges
- **Lightweight** - ~450 lines vs thousands with Carbon
- **WordPress Native** - Built on `wp_date()`, `get_gmt_from_date()`, etc.

## Installation

```bash
composer require arraypress/wp-date-utils
```

## Basic Usage

```php
use ArrayPress\DateUtils\Dates;

// Current times
$utc_now   = Dates::now_utc();        // For database storage
$local_now = Dates::now_local();      // For display

// Convert between UTC and local
$utc   = Dates::to_utc( $user_input );           // Store in DB
$local = Dates::to_local( $database_value );     // Display to user

// Format dates
echo Dates::format( $utc_date );                 // Uses WP settings
echo Dates::format( $utc_date, 'date' );         // Date only
echo Dates::format( $utc_date, 'time' );         // Time only

// Human-readable
echo Dates::human_diff( $utc_date );  // "2 hours ago"

// Format with empty handling
echo Dates::format_or_empty( $date );            // Shows "—" if empty
echo Dates::format_or_empty( $date, 'Never', 'human' );  // "Never" for empty dates
```

## Date Ranges

```php
// Get predefined ranges (returns UTC start/end)
$range = Dates::get_range( 'today' );
$range = Dates::get_range( 'last_week' );
$range = Dates::get_range( 'last_30_days' );
$range = Dates::get_range( 'year_to_date' );

// Essential ranges available:
// today, yesterday
// this_week, last_week
// this_month, last_month
// this_year, last_year
// last_7_days, last_30_days, last_90_days
// year_to_date

// Returns: ['start' => '2025-01-01 00:00:00', 'end' => '2025-01-31 23:59:59']

// Ranges calculate in local timezone by default
$today = Dates::get_range( 'today' );  // Today in site timezone, returned as UTC

// Or use pure UTC calculation (for rolling windows)
$last_30 = Dates::get_range( 'last_30_days', false );  // Pure UTC
```

## Date Math

```php
// Add time (uses precise DateTime for months/years)
$future = Dates::add( $utc_date, 7, 'days' );
$future = Dates::add( $utc_date, 2, 'hours' );
$future = Dates::add( $utc_date, 1, 'months' );   // Precise month calculation
$future = Dates::add( $utc_date, 1, 'years' );    // Handles leap years

// Subtract time
$past = Dates::subtract( $utc_date, 30, 'days' );
$past = Dates::subtract( $utc_date, 6, 'months' );

// Direct month/year arithmetic
$next_month = Dates::add_months( $utc_date, 1 );
$next_year = Dates::add_years( $utc_date, 1 );

// Get difference
$days  = Dates::diff( $date1, $date2, 'days' );
$hours = Dates::diff( $date1, $date2, 'hours' );

// Get elapsed time
$hours_ago = Dates::elapsed( $utc_date, 'hours' );  // null if invalid

// Calculate expiration
$expires = Dates::calculate_expiration( 30, 'days' );
$expires = Dates::calculate_expiration( 1, 'years' );
```

## Validation & Checks

```php
// Date validation
if ( Dates::is_valid( $date_string ) ) {}
if ( Dates::is_format( $date, 'Y-m-d' ) ) {}

// Date checks
if ( Dates::is_expired( $utc_date ) ) {}
if ( Dates::is_expired( $utc_date, 24 ) ) {}  // With 24-hour grace period
if ( Dates::is_future( $utc_date ) ) {}
if ( Dates::is_past( $utc_date ) ) {}
if ( Dates::is_zero( $date ) ) {}  // Check for 0000-00-00
if ( Dates::in_range( $date, $start, $end ) ) {}

// Freshness checks
if ( Dates::is_stale( $last_sync, 24, 'hours' ) ) {
    // Data is older than 24 hours or empty
}

if ( Dates::is_fresh( $cache_time, 1, 'hours' ) ) {
    // Cache is less than 1 hour old and valid
}

// Age calculation
$age = Dates::get_age( $birth_date_utc );
```

## Business Logic

```php
// Weekend/business day checking
if ( Dates::is_weekend( $utc_date ) ) {}
if ( Dates::is_business_day( $utc_date ) ) {}

// Custom business days (1=Mon, 7=Sun)
$custom_days = [1, 2, 3, 4, 5, 6]; // Mon-Sat
if ( Dates::is_business_day( $utc_date, $custom_days ) ) {}

// Get next business day
$next = Dates::next_business_day( $utc_date );
$next = Dates::next_business_day( $utc_date, [1, 2, 3, 4, 5] );
```

## Date Boundaries

```php
// Get start/end of day
$start = Dates::start_of_day( $utc_date );  // "2025-01-15 00:00:00"
$end = Dates::end_of_day( $utc_date );      // "2025-01-15 23:59:59"
```

## Subscriptions & Billing

```php
// Calculate next billing date (uses precise month/year arithmetic)
$next = Dates::next_billing( $last_payment, 'monthly' );
$next = Dates::next_billing( $last_payment, 'yearly' );
$next = Dates::next_billing( $last_payment, 'every_3_months' );

// Stripe-compatible periods
$periods = Dates::get_period_options();
// Returns: daily, weekly, monthly, every_3_months, every_6_months, yearly

// Include custom option for UI
$periods = Dates::get_period_options( true );  // Adds 'custom' option

// Convert to Stripe format
$stripe = Dates::get_stripe_interval( 'every_3_months' );
// Returns: ['interval' => 'month', 'interval_count' => 3]
```

## Timestamp Conversion

```php
// Convert Unix timestamp to MySQL
$mysql_date = Dates::timestamp_to_mysql( $timestamp );

// Convert MySQL to timestamp
$timestamp = Dates::to_timestamp( $mysql_date );
```

## Admin Display

```php
// Format for admin with relative time
echo Dates::format_admin( $utc_date );
// Outputs: "Jan 15, 2025 10:30 AM<br><small>2 hours ago</small>"
```

## Database Integration

```php
// Build date range queries
$query = Dates::build_date_query( 'created_at', $start_local, $end_local );

$results = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM orders WHERE {$query['sql']}",
    ...$query['values']
) );
```

## Real-World Examples

### Storing User Input
```php
// User submits date in their timezone
$user_date = $_POST['event_date'];  // "2025-01-15 14:30:00"

// Convert to UTC for database
$utc_date = Dates::to_utc( $user_date );

// Store in database
$wpdb->insert( $table, [ 'event_date' => $utc_date ] );
```

### Displaying Dates
```php
// Get from database (stored as UTC)
$event = $wpdb->get_row( "SELECT * FROM events WHERE id = 1" );

// Display in user's timezone
echo Dates::format( $event->event_date );      // "Jan 15, 2025 2:30 PM"
echo Dates::human_diff( $event->created_at );  // "3 days ago"

// Display with empty handling
echo Dates::format_or_empty( $event->deleted_at, 'Not deleted', 'human' );
```

### Date Range Queries
```php
// User selects "Last 30 Days" 
$range = Dates::get_range( 'last_30_days' );

// Query database with proper UTC dates
$results = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM orders WHERE created_at BETWEEN %s AND %s",
    $range['start'],
    $range['end']
) );
```

### Data Freshness Checking
```php
class API_Cache {
    public function get_data() {
        $cached_at = get_option( 'api_cache_time' );
        
        // Check if cache is stale (older than 1 hour)
        if ( Dates::is_stale( $cached_at, 1, 'hours' ) ) {
            $data = $this->fetch_from_api();
            update_option( 'api_cache_time', Dates::now_utc() );
            return $data;
        }
        
        return get_option( 'api_cache_data' );
    }
}
```

### Subscription Management
```php
// Calculate next billing with Stripe-compatible periods
$subscription = get_subscription( $user_id );
$next_billing = Dates::next_billing( $subscription->last_payment, 'every_3_months' );

// Convert for Stripe API
$stripe_interval = Dates::get_stripe_interval( 'every_3_months' );
\Stripe\Subscription::create([
    'customer' => $customer_id,
    'items' => [['price' => $price_id]],
    'recurring' => $stripe_interval
]);

// Check if expired with grace period
if ( Dates::is_expired( $subscription->expires_at, 72 ) ) {  // 72-hour grace
    cancel_subscription( $user_id );
}
```

### Admin Table Column
```php
public function column_created( $item ) {
    // Properly converts UTC to local and formats
    return Dates::format( $item['created_at'] );
    
    // Or with admin formatting (includes relative time)
    return Dates::format_admin( $item['created_at'] );
}

public function column_last_sync( $item ) {
    // Shows "Never" for empty dates
    return Dates::format_or_empty( $item['last_sync_at'], 'Never', 'human' );
}
```

## Method Reference

### Core Operations
- `now_utc()` - Current UTC time
- `now_local()` - Current local time
- `to_utc()` - Convert local to UTC
- `to_local()` - Convert UTC to local

### Date Math
- `add()` - Add time units
- `subtract()` - Subtract time units
- `add_months()` - Precise month addition
- `add_years()` - Precise year addition
- `diff()` - Get difference between dates
- `elapsed()` - Get elapsed time since date

### Formatting
- `format()` - WordPress format with i18n
- `human_diff()` - Human readable difference
- `format_admin()` - Admin display with relative time
- `format_or_empty()` - Format with empty value handling

### Validation
- `is_valid()` - Validate date string
- `is_format()` - Check specific format
- `is_expired()` - Check expiration with grace period
- `is_future()` / `is_past()` - Time comparisons
- `is_zero()` - Check for empty/zero dates
- `in_range()` - Check if date within range
- `is_stale()` - Check if older than threshold
- `is_fresh()` - Check if within threshold

### Business Logic
- `is_weekend()` / `is_business_day()` - Day type checking
- `next_business_day()` - Skip to next business day
- `get_age()` - Calculate age from birth date

### Date Ranges
- `get_range()` - Get predefined ranges
- `range_to_utc()` - Convert local range to UTC
- `start_of_day()` / `end_of_day()` - Day boundaries

### Subscription & Billing
- `next_billing()` - Calculate next billing date
- `get_period_options()` - Get period dropdown options
- `get_stripe_interval()` - Convert to Stripe format
- `calculate_expiration()` - Calculate future expiration

### Utilities
- `timestamp_to_mysql()` - Unix timestamp to MySQL
- `to_timestamp()` - MySQL to Unix timestamp
- `build_date_query()` - Database query builder
- `get_range_options()` - Get range dropdown options

## Why Not Carbon?

This library is intentionally lightweight:

- **Performance**: No heavy dependency loading
- **Size**: ~450 lines vs Carbon's thousands
- **Simplicity**: Does exactly what WordPress needs
- **Native**: Uses WordPress's own date functions
- **Sufficient**: Covers 99% of WordPress date needs
- **Maintenance**: Fewer dependencies to manage

## Requirements

- PHP 7.4 or higher
- WordPress 5.3 or higher (for `wp_timezone()`)

## License

GPL-2.0-or-later

## Support

- [Documentation](https://github.com/arraypress/wp-dates)
- [Issue Tracker](https://github.com/arraypress/wp-dates/issues)