<?php

	loadlib("export");

	##############################################################################

	function export_csv(&$checkins, $fh=null){

		if (! $fh){
			$fh = export_default_filehandle();
		}

		fputcsv($fh, array_keys($checkins[0]));

		foreach ($checkins as $row){
			export_massage_checkin($row);
			fputcsv($fh, array_values($row));
		}

		return okay(array('fh' => $fh));
	}

	##############################################################################

?>
