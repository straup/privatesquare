<?php

 	#################################################################

	function foursquare_urls_venue(&$venue){

		return "http://www.foursquare.com/venue/{$venue['venue_id']}/";
	}

 	#################################################################

	function foursquare_urls_checkin(&$checkin){

		# see the way I named foursquare checkin IDs 'checkin_id'
		# yeah, that was awesome... (20120219/straup)

		if (! $checkin['checkin_id']){
			return;
		}

		$user = users_get_by_id($checkin['user_id']);
		$fsq_user = foursquare_users_get_by_user_id($user['id']);

		# Note the lack of a trailing slash, which is apparently
		# important in foursquare land...

		return "http://www.foursquare.com/user/{$fsq_user['foursquare_id']}/checkin/{$checkin['checkin_id']}";
	}

 	#################################################################
?>
