<?php

	loadlib("privatesquare_export");

	##############################################################################

	function privatesquare_export_csv_row($row, $fh, $more=array()){

		$defaults = array(
			'index' => 0,
			'send_headers' => 1
		);

		$more = array_merge($defaults, $more);

		privatesquare_export_massage_checkin($row);

		if (($more['index'] == 1) && ($more['send_headers'])){

			$map = privatesquare_export_valid_formats();

			$headers = array(
				'Content-type' => $map['csv'],
			);

			privatesquare_export_send_headers($headers);
		}

		if ($more['index'] == 1){
			fputcsv($fh, array_keys($row));
		}

		fputcsv($fh, array_values($row));
	}

	# deprecated (20131125/straup)

	function privatesquare_export_csv($fh, $checkins, $more=array()){

		# TO DO: this works fine until we want to "explode" specific
		# fields (like 'weather') because we need to find all the distinct
		# keys that might be used (across both fields and potential
		# providers: google weather versus some other api). This is
		# either a head scratch or a pain in the ass or both...
		# (2012027/straup)

		$header = 0;

		foreach ($checkins as $row){
			
			privatesquare_export_massage_checkin($row);

			if (! $header){
				fputcsv($fh, array_keys($row));
				$header = 1;
			}

			fputcsv($fh, array_values($row));
		}

		if (isset($more['donot_send'])){
			return okay();
		}

		$map = privatesquare_export_valid_formats();

		$headers = array(
			'Content-type' => $map['csv'],
		);

		privatesquare_export_send($fh, $headers, $more);
	}

	##############################################################################

	# the end
