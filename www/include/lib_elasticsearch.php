<?php

	loadlib("http");

	$GLOBALS['timing_keys']['es_queries'] = 'ElasticSearch Queries';
	$GLOBALS['timings']['es_queries_count']	= 0;
	$GLOBALS['timings']['es_queries_time']	= 0;

	$GLOBALS['timing_keys']['es_mget'] = 'ElasticSearch Multi-GET requests';
	$GLOBALS['timings']['es_mget_count']	= 0;
	$GLOBALS['timings']['es_mget_time']	= 0;

	########################################################################

	function elasticsearch_search($query, $more=array()){


		$defaults = array(
			'host' => $GLOBALS['cfg']['elasticsearch_host'],
			'port' => $GLOBALS['cfg']['elasticsearch_port'],
			'search_type' => 'query_then_fetch',
			'search_pipeline' => null,
			'http_timeout' => $GLOBALS['cfg']['elasticsearch_http_timeout'],
			'per_page' => $GLOBALS['cfg']['pagination_per_page'],
			'page' => 1,
			'index' => null,
			'type' => null,

			'scroll' => $GLOBALS['cfg']['elasticsearch_scroll'],
			'scroll_ttl' => $GLOBALS['cfg']['elasticsearch_scroll_ttl'],
			'scroll_id' => null,
			'cursor' => null,
		);


		$more = array_merge($defaults, $more);

		# see also: https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-search-after.html
		# which is not available in versions of ES < 5.0, and also:
		#
		# "search_after is not a solution to jump freely to a random page but rather to scroll many queries in parallel.
		# It is very similar to the scroll API but unlike it, the search_after parameter is stateless, it is always resolved
		# against the latest version of the searcher. For this reason the sort order may change during a walk depending on
		# the updates and deletes of your index."
		#
		# (20190109/thisisaaronland)

		if ((! $more["scroll_id"]) && ($more["cursor"])){
			$more["scroll_id"] = $more["cursor"];
		}

		# http://www.elasticsearchtutorial.com/basic-elasticsearch-concepts.html
		# The amount of time it took me to figure out that it's 'size' and 'from'
		# instead of 'rows' and 'start' or 'page' and 'per_page' was a bit
		# depressing really (20140206/straup)

		$page = max(1, $more['page']);
		$per_page = max(1, $more['per_page']);

		$from = ($page - 1) * $per_page;
		$size = $per_page;

		$get_args = array(
			'size' => $size,
			'from' => $from,
			'search_type' => $more['search_type'],
		);

		if ($pipeline = $more["search_pipeline"]){
			$get_args["search_pipeline"] = $pipeline;	
		}

		$http_more = array(
			'http_timeout' => $more['http_timeout'],
		);

		if (($exclude = $more["exclude"]) && (is_array($exclude))){

			$query["_source"] = array(
				"exclude" => $exclude,
			);
		}

		$body = json_encode($query);

		$headers = array(
			"Content-Type" => "application/json",
		);

		# I hate this... but basically a user shouldn't have to think about
		# whether or not something will require a cursor so we check to see
		# for them... because, computers... (20170227/thisisaaronland)

		$pre_count = 0;

		# this was an attempt to use page-based pagination up to the point at
		# which the cursor kicks (after whatever page triggers the ES max
		# documents flag) and then use the cursor but there is no way to tell
		# a cursor to start at offset (x) so far as I can tell - basically we
		# want to be using the ES 5.x "search_after" stuff but we need to update
		# everything to work with ES 5/6 queries first... leaving this here in
		# case I come up with something and as a caution to the next person
		# thinking about the nightmare of pagination in ES...
		# (20190117/thisisaaronland)
		
		$scroll_eventually = 0;

		# Generally we want or need to precount things...

		if ((! $more['scroll'])	|| ($page == 1)){
			$pre_count = 1;
		}

		# But wait! There's more!! We _don't_ want to precount things
		# if we're faceting because the scroll stuff confuses the faceting
		# stuff. Because we can't have nice things...

		if ((isset($query["aggregations"])) || (isset($query["aggs"]))){
			$more['scroll'] = 0;
			$pre_count = 0;
		}

		if ($pre_count){

			$_args = $get_args;
			$_args['size'] = 0;

			$_url = $more['host'];

			if ($more['port']){
			   	$host = $more["host"];
				$port = $more["port"];			      
				$host = rtrim($host, "/");
				$_url = "{$host}:{$port}/";
			}

			if ($more['index']){
				$_url .= "{$more['index']}/";
			}

			if ($more['type']){
				$_url .= "{$more['type']}/";
			}
			
			$_query = http_build_query($_args);
			$_url .= "_search/?{$_query}";

			$_rsp = http_post($_url, $body, $headers, $http_more);

			if (! $_rsp['ok']){
				return $_rsp;
			}

			$_data = json_decode($_rsp['body'], 'as hash');

			if (! $_data){
				return array('ok' => 0, 'error' => 'failed to decode JSON');
			}

			if ($_data['error']){
				return array('ok' => 0, 'error' => $_data['error']);
			}

			$_hits = $_data["hits"];
			$_count = $_hits["total"]["value"];

			# TO DO : remove spelunker-iness and make this a general purpose flag
			# (20170227/thisisaaronland)

			$trigger = $GLOBALS['cfg']['elasticsearch_scroll_trigger'];

			if ($_count >= $trigger){

				$more['scroll'] = 1;

				# tl;dr - none of this works... I think - leaving it here
				# for now because I'd like to believe that is does work but
				# I just haven't figured out how yet...
				# (20190117/thisisaaronland)
				#
				# but wait! don't bother doing scroll based pagination until
				# we actually need to (20190111/thisisaaronland)
				#
				# $per_page = $more["per_page"];
				# $max_page = floor($trigger / $per_page);
				#
				# the problem here is that we still want to generate a cursor is 
				# for linking to page XXX but we don't want to 
				#
				# if ($page >= $max_page){
				# 	$more['scroll'] = 1;
				# } else {
				# 	$scroll_eventually = 1;
				# }

			}

			else {
				$more["scroll"] = 0;
			}
		}

		# End of I hate this... for now 

		$url = $more['host'];

		if ($more['port']){
		   	$host = $more["host"];
			$port = $more["port"];			      
			$host = rtrim($host, "/");
			$url = "{$host}:{$port}/";
		}

		if ($more['index']){
			$url .= "{$more['index']}/";
		}

		if ($more['type']){
			$url .= "{$more['type']}/";
		}

		# https://www.elastic.co/guide/en/elasticsearch/guide/current/scroll.html

		if (($more['scroll']) && ($more['scroll_id'])){

			$query = array(
				"scroll" => $more['scroll_ttl'],
				"scroll_id" => $more['scroll_id'],
			);

			$body = json_encode($query);

			# See the way we're removing the index? Yeah... that

		   	$host = $more["host"];
			$host = rtrim($host, "/");
			
			$port = $more["port"];

			if ($port){
				$url = "{$host}:{$port}/";
			}

			else {
				$url = "{$host}/";
			}

			$url .= "_search/scroll";
		}

		else if ($more['scroll']){

			$get_args["scroll"] = $more["scroll_ttl"];
			$get_query = http_build_query($get_args);

			$url .= "_search/?{$get_query}"; 
		}

		else {

			$get_query = http_build_query($get_args);
			$url .= "_search?{$get_query}";
		}

		$start = microtime_ms();

		$rsp = http_post($url, $body, $headers, $http_more);

		$end = microtime_ms();

		$GLOBALS['timings']['es_queries_count']	+= 1;
		$GLOBALS['timings']['es_queries_time'] += $end-$start;

		if (! $rsp['ok']){
			return $rsp;
		}

		$data = json_decode($rsp['body'], 'as hash');

		if (! $data){
			return array('ok' => 0, 'error' => 'failed to decode JSON');
		}

		# dumper($data);

		if($data['error']) {
			return array('ok' => 0, 'error' => $data['error']);
		}

		$rows = elasticsearch_rowinate_search_results($data);
		$pagination = elasticsearch_paginate_search_results($data, $page, $per_page);

		$rsp['rows'] = $rows;
		$rsp['pagination'] = $pagination;
		$rsp['aggregations'] = array();

		if ($a = $data['aggregations']){

			foreach ($a as $bucket => $details){

				$rsp["aggregations"][$bucket] = array();

				$keys = array_keys($details);

				// To account for value counts 
				// https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-valuecount-aggregation.html

				if (array_key_exists("value", $details)){
					$rsp["aggregations"][$bucket]["count"] = $details["value"];   
				}

				else if (array_key_exists("buckets", $details)){

					foreach ($details["buckets"] as $b){
						if (isset($b["key_as_string"])){
							$rsp["aggregations"][$bucket][$b["key_as_string"]] = $b["doc_count"];
						} else {
							$rsp["aggregations"][$bucket][$b["key"]] = $b["doc_count"];					
						}
					}

				} else {}

			}
		}

		# this depends on code above with doesn't work / is disabled
		# leaving it here for the time being with the vain hope that
		# maybe it will work one day... (20190116/thisisaaronland)

		if ((0) && ($scroll_eventually)){

			$get_args["scroll"] = $more["scroll_ttl"];
			$get_args["size"] = 0;

			$get_query = http_build_query($get_args);

			$url = explode("?", $url, 2);
			$url = $url[0];

			$url .= "/_search/?{$get_query}"; 
			$scroll_rsp = http_post($url, $body, $headers, $http_more);

			$data = json_decode($scroll_rsp['body'], 'as hash');
			$cursor = $data["_scroll_id"];

			$pagination["cursor"] = $cursor;
			$rsp["pagination"] = $pagination;
		}

		# log_notice('elasticsearch', $url . ' ' . $body . " HTTP {$rsp['info']['http_code']}", $rsp['info']['total_time']);

		$rsp['_query'] = $query;
		return $rsp;
	}

	########################################################################

	function elasticsearch_facet($field, $more=array()){

		$query_all = array(
			"match_all" => new stdClass
		);

		$defaults = array(
			'query' => $query_all,
			# 'search_type' => 'count',
			'include_filter' => null,
			'exclude_filter' => null,
			'size' => 1000,
		);

		$more = array_merge($defaults, $more);

		$aggrs = array(
			"facet" => array(
				'terms' => array('field' => $field, 'size' => $more["size"])
			)
		);

		if ($more['include_filter']){
			$aggrs['facet']['terms']['include'] = $more['include_filter'];
		}

		if ($more['exclude_filter']){
			$aggrs['facet']['terms']['exclude'] = $more['exclude_filter'];
		}

		if ($more['order']){
			$aggrs['facet']['terms']['order'] = $more['order'];
		}

		$req = array(
			'query' => $more['query'],
			'aggs' => $aggrs,
		);

		# $t1 = microtime_ms();

		$rsp = elasticsearch_search($req, $more);

		# $t2 = microtime_ms();

		if (! $rsp['ok']){
			return $rsp;
		}

		$body = $rsp['body'];
		$body = json_decode($body, 'as hash');

		if (! $body){
			return array("ok" => 0, "error" => "failed to parse response");
		}

		$aggrs = $body['aggregations'];
		$facets = $aggrs['facet'];
		$facets = $facets['buckets'];

		$rsp = elasticsearch_paginate_aggregation_results($facets, $more);

		$pagination = $rsp['pagination'];
		$rows = $rsp['aggregations'];
		
		return array("ok" => 1, "rows" => $rows, "pagination" => $pagination);
	}

	########################################################################

	# https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-multi-get.html

	function elasticsearch_mget($ids, $more=array()){

		$defaults = array(
			'host' => $GLOBALS['cfg']['elasticsearch_host'],
			'port' => $GLOBALS['cfg']['elasticsearch_port'],
			'http_timeout' => $GLOBALS['cfg']['elasticsearch_http_timeout'],
			# 'per_page' => $GLOBALS['cfg']['pagination_per_page'],
			# 'page' => 1,
			# 'index' => null,
			# 'type' => null,
			'fields' => null,
		);

		$more = array_merge($defaults, $more);

		$query = array( 'ids' => $ids );

		if ($fields = $more['fields']) {

			$docs = array();

			foreach ($ids as $id){

				$docs[] = array(
					'_id' => $id,
					'_source' => $fields,
				);
			}

			$query = array(
				'docs' => $docs
			);
		}

		$query = json_encode($query);

		$url = $more["host"];

		if ($more['port']){
		   	$host = $more["host"];
			$port = $more["port"];			      
			$host = rtrim($host, "/");
			$url = "{$host}:{$port}/";
		}

		if ($more['index']){
			$url .= "{$more['index']}/";
		}

		if ($more['type']){
			$url .= "{$more['type']}/";
		}

		$url .= "_mget/";

		$headers = array(
			"Content-Type" => "application/json",
		);

		# the following requires a copy of lib_http >= this commit:
		# https://github.com/whosonfirst/flamework/commit/1c552608169c3cebedbd333efe71a5928eaac60a
		# (20161031/thisisaaronland)

		$http_more = array(
			'http_timeout' => $more['http_timeout'],
			'body' => $query
		);

		$start = microtime_ms();

		$rsp = http_get($url, $headers, $http_more);

		$end = microtime_ms();

		$GLOBALS['timings']['es_mget_count'] += 1;
		$GLOBALS['timings']['es_mget_time'] += $end-$start;

		if (! $rsp['ok']){
			return $rsp;
		}

		$data = json_decode($rsp['body'], 'as hash');

		if (! $data){
			return array('ok' => 0, 'error' => 'failed to decode JSON');
		}

		if ($data['error']){
			return array('ok' => 0, 'error' => $data['error']);
		}

		$rows = elasticsearch_rowinate_mget_results($data);

		/*
		if (count($rows) != count($data["docs"])){

			return array(
				"ok" => 0,
				"error" => "One or more records is missing",
			);
		}
		*/
		return array(
			'ok' => 1,
			'rows' => $rows,
		);
	}

	########################################################################

	function elasticsearch_get_index_record($id, $more=array()) {

		$defaults = array(
			"host" => $GLOBALS['cfg']['elasticsearch_host'],
			"port" => $GLOBALS['cfg']['elasticsearch_port'],
			"index" => "collection",
			"type" => "objects"
		);

		$more = array_merge($defaults, $more);

		$url = $more["host"];

		if ($more['port']){
		   	$host = $more["host"];
			$port = $more["port"];			      
			$host = rtrim($host, "/");
			$url = "{$host}:{$port}/";
		}

		if ($more['index']){
			$url .= "{$more['index']}/";
		}

		if ($more['type']){
			$url .= "{$more['type']}/";
		}

		if ($id){
			$url .= "{$id}/";
		}

		$start = microtime_ms();

		$rsp = http_get($url);

		$end = microtime_ms();

		$GLOBALS['timings']['es_queries_count']	+= 1;
		$GLOBALS['timings']['es_queries_time'] += $end-$start;

		log_notice('elasticsearch', $url . " HTTP {$rsp['info']['http_code']}", $rsp['info']['total_time']);

		$rsp = json_decode($rsp['body'], 'as array');
		return($rsp['_source']);
	}

	########################################################################

	function elasticsearch_rowinate_search_results(&$data){

		$rows = array();

		foreach ($data['hits']['hits'] as $h){
			$rows[] = $h['_source'];
		}

		return $rows;
	}

	########################################################################

	function elasticsearch_rowinate_mget_results(&$data){

		$rows = array();

		foreach ($data['docs'] as $h){

			if (! $h["found"]){
				continue;
			}

			$rows[] = $h['_source'];
		}

		return $rows;
	}

	########################################################################

	function elasticsearch_paginate_search_results(&$data, $page, $per_page){

		$hits = $data["hits"];
		$total = $hits["total"];
		$total = $total["value"];
		
		$total_count = $total;
		$page_count = ceil($total_count / $per_page);
		$last_page_count = $total_count - (($page_count - 1) * $per_page);

		$pagination = array(
			'total_count' => $total_count,
			'page' => $page,
			'per_page' => $per_page,
			'page_count' => $page_count,
		);

		if (isset($data["_scroll_id"])){

			$cursor = $data["_scroll_id"];
			$pagination["cursor"] = $cursor;

			# Why do I have to do this? I thought the whole point of cursors
			# was not to have to do this... (20170302/thisiarronland)

			if (count($data['hits']['hits']) == 0){

				# The other thing is that it's important this be an empty string
				# and not a null value because the test for generating next_query
				# in api_utils_ensure_pagination_results() is by testing whether
				# 'if (isset($pagination['cursor'])){'. I'm not saying that's the
				# best way to do it only that it's the way we do it today. If
				# there's a better way then we should do that instead...
				# (20170302/thisisaaronland)

				$pagination["cursor"] = "";
			}

			if ($total_count <= $per_page){
				unset($pagination["cursor"]);
			}
		}

		return $pagination;
	}

	########################################################################

	function elasticsearch_reset_index($index, $settings=null, $mappings=null, $more=array()) {
		$defaults = array(
			'host' => $GLOBALS['cfg']['elasticsearch_host'],
			'port' => $GLOBALS['cfg']['elasticsearch_port']
		);

		if (!$settings) {
			$settings = new stdClass;
		}

		if (!$mappings) {
			$mappings = new stdClass;
		}

		$more = array_merge($defaults, $more);

		$deleteRsp = elasticsearch_delete_index($index, $more);
		$createRsp = elasticsearch_create_index($index, $settings, $mappings, $more);

		$rsp = array(
			'delete' => $deleteRsp,
			'create' => $createRsp
		);

		return $rsp;
	}

	########################################################################

	function elasticsearch_create_index($index, $settings=null, $mappings=null, $more=array()) {
		$defaults = array(
			'host' => $GLOBALS['cfg']['elasticsearch_host'],
			'port' => $GLOBALS['cfg']['elasticsearch_port']
		);

		if (!$settings) {
			$settings = new stdClass;
		}

		if (!$mappings) {
			$mappings = new stdClass;
		}

		$more = array_merge($defaults, $more);

		$url = implode(":", array($more['host'], $more['port']));
		$url .= "/{$index}";

		$body = array(
			"settings" => $settings,
			"mappings" => $mappings
		);

		$body = json_encode($body);

		$start = microtime_ms();

		$rsp = http_post($url, $body);

		$end = microtime_ms();

		$GLOBALS['timings']['es_queries_count']	+= 1;
		$GLOBALS['timings']['es_queries_time'] += $end-$start;

		log_notice('elasticsearch', $url . ' ' . $body . " HTTP {$rsp['info']['http_code']}", $rsp['info']['total_time']);

		return $rsp;
	}	

	########################################################################

	function elasticsearch_delete_index($index, $more=array()) {
		$defaults = array(
			'host' => $GLOBALS['cfg']['elasticsearch_host'],
			'port' => $GLOBALS['cfg']['elasticsearch_port']
		);

		$more = array_merge($defaults, $more);

		$url = implode(":", array($more['host'], $more['port']));
		$url .= "/{$index}";
		
		$start = microtime_ms();

		$rsp = http_delete($url, null);

		$end = microtime_ms();

		$GLOBALS['timings']['es_queries_count']	+= 1;
		$GLOBALS['timings']['es_queries_time'] += $end-$start;

		log_notice('elasticsearch', $url . " HTTP {$rsp['info']['http_code']}", $rsp['info']['total_time']);

		return $rsp;
	}

	########################################################################

	function elasticsearch_update_document($index, $type, $id, $update=array(), $more=array()) {
		$defaults = array(
			'host' => $GLOBALS['cfg']['elasticsearch_host'],
			'port' => $GLOBALS['cfg']['elasticsearch_port']
		);

		$more = array_merge($defaults, $more);

		$url = implode(":", array($more['host'], $more['port']));
		$url .= "/{$index}/{$type}/{$id}/_update";

		$body = array("doc" => $update);
		$body = json_encode($body);

		$headers = array(
			"Content-Type" => "application/json",
		);

		$start = microtime_ms();

		$rsp = http_post($url, $body, $headers);

		$end = microtime_ms();

		$GLOBALS['timings']['es_queries_count']	+= 1;
		$GLOBALS['timings']['es_queries_time'] += $end-$start;

		log_notice('elasticsearch', $url . ' ' . $body . " HTTP {$rsp['info']['http_code']}", $rsp['info']['total_time']);

		return $rsp;
	}	

	########################################################################	

	function elasticsearch_add_documents($docs, $index, $type, $more=array()){

		$defaults = array(
			'host' => $GLOBALS['cfg']['elasticsearch_host'],
			'port' => $GLOBALS['cfg']['elasticsearch_port'],
			'id_field' => 'id'
		);

		$more = array_merge($defaults, $more);

		$http_more = array(
			'http_timeout' => $GLOBALS['cfg']['elasticsearch_http_timeout'],
		);

		$data = array(
			"index" => array(
				"_id" => null
			)
		);

		$body = "";

		foreach ($docs as $doc) {
			$data["index"]["_id"] = $doc[$more['id_field']];

			/* 
			bulk api follows the format of instruction, new line, doc, new line, until complete.
			see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-bulk.html
			*/
			$doc_string = json_encode($data) . "\n" . json_encode($doc) . "\n";

			$body .= $doc_string;
		}

		$url = implode(":", array($more['host'], $more['port']));
		$url .= "/{$index}/{$type}/_bulk";

		$headers = array(
			"Content-Type" => "application/json",
		);

		$start = microtime_ms();

		$rsp = http_post($url, $body, $headers, $http_more);

		$end = microtime_ms();

		$GLOBALS['timings']['es_queries_count']	+= 1;
		$GLOBALS['timings']['es_queries_time'] += $end-$start;

		log_notice('elasticsearch', $url . ' ' . $body . " HTTP {$rsp['info']['http_code']}", $rsp['info']['total_time']);

		return $rsp;
	}

	########################################################################

	function elasticsearch_delete_documents($docs, $index, $type, $more=array()){

		$defaults = array(
			'host' => $GLOBALS['cfg']['elasticsearch_host'],
			'port' => $GLOBALS['cfg']['elasticsearch_port'],
			'id_field' => 'id'
		);

		$more = array_merge($defaults, $more);
		
		$data = array(
			"delete" => array(
				"_id" => null
			)
		);

		$body = "";

		foreach($docs as $doc) {
			$data["delete"]["_id"] = $doc[$more['id_field']];
			$doc_string = json_encode($data) . "\n";

			$body .= $doc_string;
		}

		$url = implode(":", array($more['host'], $more['port']));
		$url .= "/{$index}/{$type}/_bulk";

		$headers = array(
			"Content-Type" => "application/json",
		);

		$start = microtime_ms();

		$rsp = http_post($url, $body, $headers);

		$end = microtime_ms();

		$GLOBALS['timings']['es_queries_count']	+= 1;
		$GLOBALS['timings']['es_queries_time'] += $end-$start;

		log_notice('elasticsearch', $url . ' ' . $body . " HTTP {$rsp['info']['http_code']}", $rsp['info']['total_time']);

		return $rsp;
	}

	########################################################################

	/*
		ensures the and filter and the bool->must query contain a match_all 
		if they are empty. they will always return 0 results otherwise.

		json_encode turns array() into [], and new stdClass into {}
		since match_all requires an empty object, {}, i use new stdClass below.
	*/

	function elasticsearch_ensure_complete_query(&$query){

		//if the and filter is still empty, match all
		if(count($query['query']['filtered']['filter']['and']) == 0) {
			$query['query']['filtered']['filter']['and'][] = array(
				"match_all" => new stdClass
			);
		}

		//if the bool.must query is empty, match all
		if(count($query['query']['filtered']['query']['bool']['must']) == 0) {
			$query['query']['filtered']['query']['bool']['must'][] = array(
				"match_all" => new stdClass
			);
		}

		//note the pass by ref
	}

	########################################################################

	function elasticsearch_paginate_aggregation_results($results, $more=array()){

		$page = isset($more['page']) ? max(1, $more['page']) : 1;
		$per_page = isset($more['per_page']) ? max(1, $more['per_page']) : $GLOBALS['cfg']['pagination_per_page'];

		$offset = ($page - 1) * $per_page;

		$total_count = count(array_keys($results));
		$page_count = ceil($total_count / $per_page);
		$last_page_count = $total_count - (($page_count - 1) * $per_page);

		$pagination = array(
			'total_count' => $total_count,
			'page' => $page,
			'per_page' => $per_page,
			'page_count' => $page_count,
		);

		$results = array_slice($results, $offset, $per_page, 'preserve keys');

		return array(
			'ok' => 1,
			'aggregations' => $results,
			'pagination' => $pagination,
		);		
	}

	########################################################################

	function elasticsearch_escape($str, $more=array()){

		$defaults = array(
			"to_allow" => array()
		);

		$more = array_merge($defaults, $more);

		if (is_numeric($str)){
			return intval($str);
		}
	
	        # If you need to use any of the characters which function as operators in
        	# your query itself (and not as operators), then you should escape them
	        # with a leading backslash. For instance, to search for (1+1)=2, you would
        	# need to write your query as \(1\+1\)\=2.
	        #
        	# The reserved characters are: + - = && || > < ! ( ) { } [ ] ^ " ~ * ? : \ /
	        #
        	# Failing to escape these special characters correctly could lead to a
	        # syntax error which prevents your query from running.
		#
		# A space may also be a reserved character. For instance, if you have a
		# synonym list which converts "wi fi" to "wifi", a query_string search for
		# "wi fi" would fail. The query string parser would interpret your query
		# as a search for "wi OR fi", while the token stored in your index is
		# actually "wifi". Escaping the space will protect it from being touched
		# by the query string parser: "wi\ fi"
		#
		# https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html

		# note the absence of "&" and "|" which are handled separately

		$to_escape = array(
			"+", "-", "=", ">", "<", "!", "(", ")", "{", "}", "[", "]", "^", '"', "~", "*", "?", ":", "\\", "/"
		);

		$escaped = array();

		# $chars = preg_split('/(?<!^)(?!$)/u', $str);

		$chars = array();
		$strlen = mb_strlen($str); 

		while ($strlen) { 
			$chars[] = mb_substr($str, 0, 1, "UTF-8"); 
			$str = mb_substr($str, 1, $strlen, "UTF-8"); 
			$strlen = mb_strlen($str); 
		}

		$count = count($chars);
		$i = 0;
		
		foreach ($chars as $char){

			if (in_array($char, $to_escape)){

				# because concordances so "gp:id" doesn't become "gp{:}id"

				if (! in_array($char, $more['to_allow'])){
					$char = "\\" . $char; 
				}
			}

			else if (in_array($char, array("&", "|"))){

				if ((($i + 1) < $count) && ($chars[ $i + 1 ] == $char)){
					$char = "\\" . $char; 
				}
			}
			
			else {
				# pass
			}

			$escaped[] = $char;
			$i++;
		}

		return implode("", $escaped);
	}
	
	########################################################################
	
	# the end
