<?php

	loadlib("privatesquare_export");

	##############################################################################

	function privatesquare_export_csv($fh, $checkins, $more=array()){

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
