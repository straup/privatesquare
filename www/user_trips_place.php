<?php

        include("include/init.php");
        loadlib("trips");
        loadlib("whereonearth");

        login_ensure_loggedin();

        $user = $GLOBALS['cfg']['user'];

        $woeid = get_int32("woeid");

        if (! $woeid){
                error_404();
        }

        $rsp = whereonearth_fetch_woeid($woeid);

        if (! $rsp['ok']){
                error_500();
        }

        $loc = $rsp['data'];

        $placetypes = array(
                'locality', 'region', 'country'
        );

        # maybe something more useful than a 404?

        if (! in_array($loc['place_type'], $placetypes)){
                error_404();
        }

        $more = array();

        $more['where'] = $loc['place_type'];
        $more['woeid'] = $woeid;

        if ($page = get_int32("page")){
                $more['page'] = $page;
        }

        $rsp = trips_get_for_user($user, $more);
        $trips = array();

        foreach ($rsp['rows'] as $row){
                trips_inflate_trip($row);
                $trips[] = $row;
        }

        $GLOBALS['smarty']->assign_by_ref("trips", $trips);
        $GLOBALS['smarty']->assign_by_ref("place", $loc);

	$enc_woeid = urlencode($woeid);

	$pagination_url = urls_user($user) . "trips/places/{$enc_woeid}/";
	$GLOBALS['smarty']->assign("pagination_url", $pagination_url);
        
        $GLOBALS['smarty']->display("page_user_trips_place.txt");
        exit();
?>
