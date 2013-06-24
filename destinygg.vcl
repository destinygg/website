backend default {
	.host = "127.0.0.1";
	.port = "8080";
}


sub vcl_recv {
	
	// allow varnish to serve stale content as needed
	set req.grace = 2m;
	
	// cache the cdn subdomain entirely
	if (req.http.host ~ "^cdn\.") {
		unset req.http.cookie;
	}
	
	// cache static assets
	if (req.url ~ "^/(js|img)/") {
		unset req.http.cookie;
	}
	
	// cache any other static asset
	if ( req.url ~ "(?i)\.(png|gif|jpeg|jpg|ico|swf|css|js|html|htm)(\?[a-z0-9]+)?$" ) {
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
	
	// Handle compression correctly. Different browsers send different
	// "Accept-Encoding" headers, even though they mostly all support the same
	// compression mechanisms. By consolidating these compression headers into
	// a consistent format, we can reduce the size of the cache and get more hits.
	// @see: http://varnish.projects.linpro.no/wiki/FAQ/Compression
	if ( req.http.Accept-Encoding ) {
		
		if ( req.http.Accept-Encoding ~ "gzip" ) {
			# If the browser supports it, we'll use gzip.
			set req.http.Accept-Encoding = "gzip";
		}
		else if ( req.http.Accept-Encoding ~ "deflate" ) {
			# Next, try deflate if it is supported.
			set req.http.Accept-Encoding = "deflate";
		}
		else {
			# Unknown algorithm. Remove it and send unencoded.
			unset req.http.Accept-Encoding;
		}
		
	}
	
	// wordpress caching
	if ( req.url ~ "^/n/" && ( req.http.cookie ~ "wordpress_logged_in" || req.url ~ "vaultpress=true" ) ) {
		return( pass );
	}
	
	// drop any cookies sent to wordpress
	if ( req.url ~ "^/n/" && req.url !~ "wp-(login|admin)" ) {
		unset req.http.cookie;
	}
	
}

sub vcl_fetch {
	
	// allow stale content if the backend is having a shitfit
	set beresp.grace = 2m;
	
	// mainly to override the cache headers sent by php
	if ( (req.url == "/" && req.http.Cookie !~ "sid=") || req.url ~ "^/[^/]\.json$") {
		set beresp.ttl = 1m;
	}
	
}