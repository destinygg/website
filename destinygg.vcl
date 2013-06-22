backend default {
	.host = "127.0.0.1";
	.port = "8080";
}


sub vcl_recv {
	
	// cache the cdn subdomain entirely
	if (req.http.host ~ "^cdn\.") {
		unset req.http.cookie;
	}
	
	// cache static assets
	if (req.url ~ "^/(js|img)/") {
		unset req.http.cookie;
	}
	
	// cache the landing page if no session was started
	if (req.url == "/" && req.http.Cookie !~ "sid=") {
		unset req.http.cookie;
	}
	
	// cache the api requests
	if (req.url ~ "^/[^/]+\.json$") {
		unset req.http.cookie;
	}
	
}

sub vcl_fetch {
	
	// mainly to override the cache headers sent by php
	if ( (req.url == "/" && req.http.Cookie !~ "sid=") || req.url ~ "^/[^/]\.json$") {
		set beresp.ttl = 1m;
	}
	
}