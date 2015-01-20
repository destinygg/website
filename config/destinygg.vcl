backend default {
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

acl purge {
	"localhost";
	// also supports IP ranges
}

sub vcl_recv {
	
	// allow purging of single cache objects from localhost
	if (req.request == "PURGE") {
		if (!client.ip ~ purge) {
			error 405 "Not allowed.";
		}
		return (lookup);
	}
	
	// allow varnish to serve stale content as needed
	set req.grace = 2m;
	
	// do not do anything with the dev/phpma/dba site
	if (req.http.host ~ "(dev|phpma)\.destiny\.gg$") {
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
	if (req.url ~ "(?i)^/(lastfm|broadcasts|twitter|youtube|stream|summoners)\.json$") {
		unset req.http.cookie;
	}
	
	// drop any cookies on the chat history file
	if ( req.url ~ "(?i)^/chat/history" ) {
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
	
	// dont cache wordpress if the user is logged in
	if ( req.http.host ~ "^blog\." && ( req.http.cookie ~ "wordpress_logged_in" || req.url ~ "vaultpress=true" ) ) {
		return( pass );
	}
	
	// drop any cookies sent to wordpress
	if ( req.http.host ~ "^blog\." && req.url !~ "wp-(login|admin)" ) {
		unset req.http.cookie;
	}
	
}

sub vcl_fetch {
	
	// allow stale content if the backend is having a shitfit
	set beresp.grace = 2m;
	
	// mainly to override the cache headers sent by php
	if ( (req.url == "/" && req.http.Cookie !~ "sid=|rememberme=") || req.url ~ "^/[^/]\.json$") {
		set beresp.ttl = 30s;
	}
	
	// do not cache the chat history for long
	if ( req.url ~ "(?i)^/chat/history" ) {
		set beresp.ttl = 200ms;
	}
	
}

sub vcl_hit {
	if (req.request == "PURGE") {
		purge;
		error 200 "Purged.";
	}
}

sub vcl_miss {
	if (req.request == "PURGE") {
		purge;
		error 200 "Purged.";
	}
}

sub vcl_hash {
	hash_data(req.url);
	if (req.http.host) {
		hash_data(req.http.host);
	} else {
		hash_data(server.ip);
	}
	hash_data(req.http.X-Forwarded-Proto);
	return (hash);
}
