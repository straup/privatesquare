<?php

 	#################################################################

	function privatesquare_checkins_export($what, $callback, $fh){

		# That's right. This is currently tied at the hip to
		# user exports. It's not a feature but it's the state
		# of things today... (20131126/straup)

		if (! isset($what['user_id'])){
			return array('ok' => 0, 'error' => 'Missing user ID');
		}

		$user = users_get_by_id($what['user_id']);
		unset($what['user_id']);

		$count_pages = null;
		$count_total = null;
		$count_index = 0;

		$args = array(
			'page' => 1,
			'per_page' => 100,
		);

		# Note the order of things here: don't overwrite
		# what we've set in $args above

		$args = array_merge($what, $args);

		while ((! isset($count_pages)) || ($args['page'] <= $count_pages)){

			# See above

			$rsp = privatesquare_checkins_for_user($user, $args);

			if (! isset($count_pages)){
				$count_pages = $rsp['pagination']['page_count'];
				$count_total = $rsp['pagination']['total_count'];
			}

			foreach ($rsp['rows'] as $row){

				$count_index ++;

				$callback_more = array(
					'index' => $count_index,
					'count' => $count_total,
					'is_first' => ($count_index == 1) ? 1 : 0,
					'is_last' => ($count_index == $count_total) ? 1 : 0
				);

				call_user_func($callback, $row, $fh, $callback_more);
			}

			# php 5.5+
			# us2.php.net/manual/en/language.generators.overview.php
			#
			# foreach ($rsp['rows'] as $row){
			# 	yield $row;
			# }

			$args['page'] += 1;
		}

		return array('ok' => 1);
	}

 	#################################################################

	# the end
