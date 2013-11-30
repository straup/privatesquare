<?php

	loadlib("privatesquare_export");

	##############################################################################

	function privatesquare_export_ics_row($row, $fh, $more=array()){

		$defaults = array(
			'send_headers' => 1
		);

		$more = array_merge($defaults, $more);

		$massage_more = array(
			'inflate_all' => 1,
		);

		privatesquare_export_massage_checkin($row, $massage_more);
		ksort($row);

		if ($more['is_first']){

			$begin = $GLOBALS['smarty']->fetch("page_export_ics_vcalendar_begin.txt");
			fwrite($fh, $begin);
		}

		$GLOBALS['smarty']->assign_by_ref("checkin", $row);
		$event = $GLOBALS['smarty']->fetch("page_export_ics_vevent.txt");
		fwrite($fh, $event);

		if (! $more['is_last']){
			$end = $GLOBALS['smarty']->fetch("page_export_ics_vcalendar_end.txt");
			fwrite($fh, $end);
		}
	}

	##############################################################################

	# the end
