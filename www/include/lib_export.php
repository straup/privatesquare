<?php

	##############################################################################

	function export_send(&$fh, $more=array()){

		$defaults = array(

		);

		$more = array_merge($defaults, $more);

		rewind($fh);

		echo stream_get_contents($fh);
	}

	##############################################################################

	function export_massage_checkin(&$row){

		unset($row['venue']);

		# note the pass-by-ref
	}

	##############################################################################
?>
