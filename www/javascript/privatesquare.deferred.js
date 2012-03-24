function privatesquare_deferred_store(checkin){

	var store = new Store("privatesquare");
	var deferred = store.get("deferred");

	if (! deferred){
		deferred = new Array();
	}
		
	deferred.push(checkin);
	store.set("deferred", deferred);
}
