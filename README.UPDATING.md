# 2.0 – the `other-venues` branch

### Add the following to your `www/include/config.php` file:

	$GLOBALS['cfg']['privatesquare_venues_providers'] = array(
		0 => 'privatesquare',
		1 => 'foursquare',
		2 => 'stateofmind',
		# 3 => 'nypl',
	);

### Apply `schema/alters/20131124.db_main.schema` to your database.

### Apply `schema/alters/20131124.db_users-pre-migration.schema` to your database.

### Run `bin/backfill_migrate_foursquare_venues.php`

### Apply `schema/alters/20131124.db_users-post-migration.schema` to your database.
