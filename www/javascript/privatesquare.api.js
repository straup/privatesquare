function privatesquare_api_call(method, args, on_success, on_error){
    
	var endpoint = privatesquare_api_endpoint();

	args['method'] = method;

	var dothis_onsuccess = function(rsp){

		if (on_success){
			on_success(rsp);
		}
	};

	var dothis_onerror = function(rsp){		    

		var parse_rsp = function(rsp){
	
			if (! rsp['responseText']){
				console.log("Missing response text");
				return;
			}

			try {
				rsp = JSON.parse(rsp['responseText']);
				return rsp;
			}

			catch (e){
				console.log("Failed to parse response text");
				return;
			}
		};

		rsp = parse_rsp(rsp);

		if (on_error){
			on_error(rsp);
		}
        };

        $.ajax({
                'url': endpoint,
                'type': 'POST',
                'data': args,
                'dataType': 'json',
                'success': dothis_onsuccess,
                'error': dothis_onerror
	});

	// console.log("calling " + args['method']);
}

function privatesquare_api_endpoint(){
	return document.body.getAttribute("data-api-endpoint");
}
