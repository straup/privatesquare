<?php

 	#################################################################

	function foursquare_checkins_broadcast_map($string_keys=0){

		$map = array(
			"" => "don't tell foursquare",
			"private" => "off the grid",
			"public" => "followers",
			"twitter" => "twitter",
			"facebook" => "facebook",
		);

		if ($string_keys){
			$map = array_flip($map);
		}

		return $map;
	}

 	#################################################################

?>
