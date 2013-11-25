# 2.0

Aka the `other-venues` branch. This branch has not been merged with `master` yet.

## In broad strokes

Changed a bunch of the backend code to allow for multiple venue "sources"
including user-defined places.

## Removed

* Removed `bin/export-user-cities.php` (replaced by `bin/export-checkins.php`)

## Updating

### Update your config file

Add the following to your `www/include/config.php` file:

	$GLOBALS['cfg']['privatesquare_venues_providers'] = array(
		0 => 'privatesquare',
		1 => 'foursquare',
		2 => 'stateofmind',
		# 3 => 'nypl',
	);

### Database alters (db_main)

Apply `schema/alters/20131124.db_main.schema` to your database.

### Database alters (db_users, part one)

Apply `schema/alters/20131124.db_users-pre-migration.schema` to your database.

### Database migration

Run `bin/backfill_migrate_foursquare_venues.php`

### Database alters (db_users, part two)

Apply `schema/alters/20131124.db_users-post-migration.schema` to your database.
