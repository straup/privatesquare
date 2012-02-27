<?php

	loadlib("privatesquare_export");

	##############################################################################

	function privatesquare_export_csv($fh, $checkins, $more=array()){

		# TO DO: this works fine until we want to "explode" specific
		# fields (like 'weather') because we need to find all the distinct
		# keys that might be used (across both fields and potential
		# providers: google weather versus some other api). This is
		# either a head scratch or a pain in the ass or both...
		# (2012027/straup)

		fputcsv($fh, array_keys($checkins[0]));

		foreach ($checkins as $row){
			privatesquare_export_massage_checkin($row);
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

?>
