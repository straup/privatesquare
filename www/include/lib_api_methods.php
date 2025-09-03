<?php

	##############################################################################

	function api_methods_can_view_method(&$method, $viewer_id=0){

		$see_all = (auth_has_role("admin", $viewer_id)) ? 1 : 0;
		$see_undocumented = (auth_has_role_any(array("admin", "api"), $viewer_id)) ? 1 : 0;

		if ((! $method['enabled']) && (! $see_all)){
			return 0;
		}

		if ((isset($method['documented_if'])) && (is_array($method['documented_if']))){

			$required = $method['documented_if'];

			if (! in_array("admin", $required)){
				$required[] = "admin";
			}

			if (! auth_has_role_any($required, $viewer_id)){
				return 0;
			}
		}

		else if ((! $method['documented']) && (! $see_all)){
			return 0;
		}

		else {}

		# see also lib_users_roles; lib_acl

		if ((isset($method['requires_capability'])) && (is_array($method['requires_capability']))){

			# It's not great that we're doing all this hoop jumping here
			# so we should reconcile this with the auth_roles stuff above
			# (20180515/thisisaaronland)

			if ($viewer_id == 0){
				return 0;
			}

			$user = users_get_by_id($viewer_id);

			if ((! $user) || ($user['deleted'])){
				return 0;
			}

			foreach ($method['requires_capability'] as $cap){

				if (! users_acl_has_capability($user, $cap)){
					return 0;
				}
			}
		}

		return 1;
	}

	##############################################################################

	# the end
