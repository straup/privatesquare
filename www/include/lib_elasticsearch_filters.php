<?php

	########################################################################

	function elasticsearch_filters_empty_boolean_conditions(){

		$conditions = array(
			"must" => array(),
			"must_not" => array(),
			"should" => array(),
		);

		return $conditions;
	}

	########################################################################

	function elasticsearch_filters_merge_boolean_conditions($filter, $conditions){

		foreach ($conditions as $match => $match_filters){

			if (! count($match_filters)){
				continue;
			}

			if (! isset($filter["bool"])){
				$filter["bool"] = array();
			}

			if (! isset($filter["bool"][$match])){
				$filter["bool"][$match] = array();
			}

			$filter["bool"][$match] = array_merge($filter["bool"][$match], $match_filters);
		}

		return $filter;	
	}

	########################################################################

	# the end