# WP Dates

Date ranges for reporting: presets, UTC boundaries, chart buckets and the
site's own clock.

## What it does

A reports screen needs the same handful of things every time — what "last 30
days" means in UTC, the equivalent window before it to compare against, the
buckets to plot, and the site's timezone and format for showing a date back.

Getting that right by hand means remembering that WordPress stores UTC and
displays local, that a range is half-open or a row lands in two months at
once, and that "3 days ago" needs the exact date in a tooltip. This does all
of it in one place.

## Features

* Turn a preset — this month, last quarter — into a start and end in UTC
* Get the matching period before it, for a comparison figure
* Build the WHERE clause and its bound parameters from a range
* Bucket a range by hour, day, week or month for a chart
* Show a date in the site's timezone and the site's format
* Say "3 days ago" with the exact date available underneath
* Freeze the clock in tests, so a date-dependent assertion is not flaky

## Installation

```bash
composer require arraypress/wp-date-utils
```

## Quick start

The window a report is showing, and the one before it:

```php
use ArrayPress\Dates\Preset;

$range    = Preset::Last30Days->range();
$previous = $range->previous();

// 2026-07-27 00:00:00 to 2026-08-26 00:00:00, in UTC.
[ $where, $params ] = $range->where( 'date_created' );

$sales = $wpdb->get_var(
	$wpdb->prepare( "SELECT SUM(total) FROM {$table} WHERE {$where}", $params )
);
```

`Preset::options()` gives the whole list, labelled, for a dropdown.

Showing a stored UTC date back to somebody:

```php
use ArrayPress\Dates\Site;

echo Site::format( $order->date_created );          // in the site's timezone
echo Site::relative_with_exact( $order->date_created ); // "3 days ago", with a title
```

## Requirements

* PHP 8.3 or later
* WordPress 7.1 or later

## License

GPL-2.0-or-later
