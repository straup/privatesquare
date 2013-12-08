<?php

 	#################################################################

	function csv_parse_file($file, $callback, $more=array()){

		$defaults = array(
			'header' => 1,
		);

		$more = array_merge($defaults, $more);

		$fh = fopen($file, 'r');

		$header = null;

		while (! feof($fh)){

			$ln = fgetcsv($fh);

			if (! $more['header']){
				$row = $ln;
			}

			else {
				if (! $header){
					$header = $ln;
					continue;
				}

				$row = array();
				$i = 0;

				foreach ($header as $k){
					$row[$k] = $ln[$i];
					$i++;			
				}
			}

			call_user_func_array($callback, array($row));
		}

	}

 	#################################################################

	# the end
