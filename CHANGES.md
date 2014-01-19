# 2.2

## In broad strokes

Trips! Dopplr!!

## Database alters (db_main)

Apply `schema/alters/20140118_db_main.schema` to your database.

## Database alters (db_users)

Apply `schema/alters/20140107_db_users.schema` to your database.

## Database migration

Dopplr's `full_data.json` files can be imported in to privatesquare a per-user
basis by running

	$> php -q bin/backfill_import_dopplr_trips.php -d full_data.json -u USER_ID

# 2.1.1

Merged in to `master` 2014-01-16

## In broad strokes

Addes `stateofmind` venues for "on a highway" and "at an intersection".

# 2.1

Merged in to `master` 20140107.

## In broad strokes

Record and display timezones for individual check-ins correctly.

## Database alters (db_main)

Apply `schema/alters/20131215.db_main.schema` to your database.

## Database alters (db_users)

Apply `schema/alters/20131208.db_users.schema` to your database.

## Database migration

Run `bin/backfill_set_privatesquare_checkins_timezones.php`

# 2.03

Merged in to `master` 20140105.

The `backfill-two-dot-oh` which fixed the `bin/sync-foursquare.php` script to
work with all the changes introduced in version 2.0.

# 2.02

"Bug fixes" which really just means I forgot to update this file at the time.

# 2.01

"Bug fixes" which really just means I forgot to update this file at the time.

# 2.0

Merged in to `master` 20131207.

## In broad strokes

Changed a bunch of the backend code to allow for multiple venue "sources"
including user-defined places.

**If you are updating privatesquare from a version older than 2.0 there are
changes that will require your time and attention. See below for details.**

## Added

* The ability to create user-defined places (venues) which may or may not have
  fixed geographies.

* The ability to check-in to "states of mind".

* The ability to check-in to historic NYPL buildings.

## Updated

* Switch to using Bootstrap 3.0 and updated the menu-ing system to actually work
  on a phone.

## Removed

* Removed `bin/export-user-cities.php` (replaced by `bin/export-checkins.php`)

* Removed support for youarehere (.spum.org) until there is time to revisit
  pairing the projects properly.

* Removed support for HTML5 offline cache because it's still a bit of a
  nightmare of edge-cases.

## Updating your config file

### New things

Add the following to your `www/include/config.php` file:

	$GLOBALS['cfg']['privatesquare_venues_providers'] = array(
		0 => 'privatesquare',
		1 => 'foursquare',
		2 => 'stateofmind',
		3 => 'nypl',
	);

### Old things (no longer necessary)

	$GLOBALS['cfg']['enable_feature_offline_appcache'] = 0;

	$GLOBALS['cfg']['enable_feature_youarehere'] = 0;
	$GLOBALS['cfg']['youarehere_api_endpoint'] = 'https://youarehere.spum.org/api/rest/';
	$GLOBALS['cfg']['youarehere_grant_endpoint'] = 'https://youarehere.spum.org/api/oauth2/authenticate/';
	$GLOBALS['cfg']['youarehere_token_endpoint'] = 'https://youarehere.spum.org/api/oauth2/access_token/';
	$GLOBALS['cfg']['youarehere_api_key'] = '';

### Database alters (db_main)

This is important. Nothing will work properly until you update the
database. Basically we're creating a new `Venues` table which will replace the
`FoursquareVenues` and we're adding a few extra columns to
`PrivatesquareCheckins` as well as changing the column type for the `venue_id`
column.

First of all just do a `mysqldump` of your existing database. It will only take a couple minutes and probably won't be necessary but won't you be glad if it is...

Apply `schema/alters/20131124.db_main.schema` to your database.

### Database alters (db_users, part one)

Apply `schema/alters/20131124.db_users-pre-migration.schema` to your database.

### Database migration

Run `bin/backfill_migrate_foursquare_venues.php`

Basically this moves all the venues listed in the FoursquareVenues table and
moves them into the Venues table assigning a provider ID (foursquare) and 
creating an artisanal venue ID. It will also update the PrivatesquareCheckins
table to point to the newly created venue ID.

Depending on where you are running this you may need to update Venues and checkins
separately. You can update all the checkins to point to the new Venues (once they've
been created) with a single SQL statement, as is:

	UPDATE PrivatesquareCheckins c, Venues v SET c.venue_id = v.venue_id WHERE c.venue_id=v.provider_venue_id;

The place where this happens in code (and which is enabled by default) is around
line 48.

### Database alters (db_users, part two)

Apply `schema/alters/20131124.db_users-post-migration.schema` to your database.

Note this *will* delete the old `FoursquareVenues` table unless you change this
file yourself.
