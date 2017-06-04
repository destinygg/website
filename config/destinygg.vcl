vcl 4.0;

backend default {
	.host = "127.0.0.1";
	.port = "8000";
}

backend dgg {
	.host = "127.0.0.1";
	.port = "8080";
	.probe = {
		.url = "/healthcheck.php";
		.timeout = 50 ms;
		.interval = 500ms;
		.window = 2;
		.threshold = 2;
	}
}

backend dgglogs {
	.host = "127.0.0.1";
	.port = "9000";
}

sub dgg_recv {
	// do not do anything with the stage/dev/sql site
	if (req.http.host ~ "(stage|dev|sql)\.destiny\.gg$") {
		return (pass);
	}

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

	// cache the landing page/chat landing page if no session was started
	if ((req.url == "/" || req.url == "/embed/chat") && req.http.Cookie !~ "sid=|rememberme=") {
		unset req.http.cookie;
	}

	// cache some of the api requests - we need stateful json for the others - until I clean it up
	if (req.url ~ "(?i)^/api/info/stream$") {
		unset req.http.cookie;
	}

	// drop any cookies on the chat history file
	if ( req.url ~ "(?i)^/api/chat/history" ) {
		unset req.http.cookie;
	}

	// dont cache wordpress if the user is logged in
	if ( req.http.host ~ "^blog\." && ( req.http.cookie ~ "wordpress_logged_in" || req.url ~ "vaultpress=true" ) ) {
		return( pass );
	}

	// drop any cookies sent to wordpress
	if ( req.http.host ~ "^blog\." && req.url !~ "wp-(login|admin)" ) {
		unset req.http.cookie;
	}
}

sub dgglogs_recv {
	// no authenticated content to care about, just get rid of all cookies
	unset req.http.cookie;

	if (req.url ~ "^/oldworlds/") {
		return (pass);
	}
}

sub vcl_recv {
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

	if (req.http.host ~ "overrustlelogs\.net$") {
		set req.backend_hint = dgglogs;
		call dgglogs_recv;
	} elseif (req.http.host ~ "destiny\.gg$") {
		set req.backend_hint = dgg;
		call dgg_recv;
	}
}

sub dgglogs_response {
	// cache static content - cloudflare should help with this
	if (bereq.url ~ "\.(jpg|jpeg|gif|png|ico|css|zip|tgz|gz|rar|bz2|pdf|tar|wav|bmp|rtf|js|flv|swf|html|htm)$") {
		set beresp.ttl = 1h;
	}

	// the content is all public so we never care about cookies
	unset beresp.http.set-cookie;

	// Set the TTL for cache object to five minutes
	set beresp.ttl = 5m;

	// allow stale content if shit gets fucked
	set beresp.grace = 24h;
}

sub dgg_response {
	// allow stale content if the backend is having a shitfit
	set beresp.grace = 1d;

	// mainly to override the cache headers sent by php
	if ( (bereq.url == "/" && bereq.http.Cookie !~ "sid=|rememberme=") || bereq.url ~ "^/[^/]\.json$") {
		set beresp.ttl = 30s;
	}

	// do not cache the chat history for long
	if ( bereq.url ~ "(?i)^/api/chat/history" ) {
		set beresp.ttl = 100ms;
	}
}

sub vcl_backend_response {
	if (bereq.http.host ~ "overrustlelogs\.net$") {
		call dgglogs_response;
	} elseif (bereq.http.host ~ "destiny\.gg$") {
		call dgg_response;
	}
}

sub vcl_hash {
	hash_data(req.http.X-Forwarded-Proto);
}

sub vcl_deliver {
	if (obj.hits > 0) {
		set resp.http.X-Cache = resp.http.Age;
	} else {
		set resp.http.X-Cache = "MISS";
	}

	unset resp.http.Age;
	unset resp.http.X-Varnish;
	unset resp.http.WP-Super-Cache;
	unset resp.http.Via;
}
