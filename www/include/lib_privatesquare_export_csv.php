<?php

	loadlib("privatesquare_export");

	##############################################################################

	function privatesquare_export_csv_row($row, $fh, $more=array()){

		$defaults = array(
			'index' => 0,
			'send_headers' => 1
		);

		$more = array_merge($defaults, $more);

		$massage_more = array(
			'flatten_all' => 1,
		);

		privatesquare_export_massage_checkin($row, $massage_more);
		ksort($row);

		if (($more['is_first']) && ($more['send_headers'])){

			$map = privatesquare_export_valid_formats();

			$headers = array(
				'Content-type' => $map['csv'],
			);

			privatesquare_export_send_headers($headers);
		}

		if ($more['is_first']){
			fputcsv($fh, array_keys($row));
		}

		fputcsv($fh, array_values($row));
	}

	##############################################################################

	# the end
