# GoosePlus

> Create your own custom !bangs - just like DuckDuckGo!  
> This project obviously isn't affiliated with DuckDuckGo in any form whatsoever - just inspired by it :-)

This project needs a better name.

## System Requirements
 - Linux (Windows may work too, but is untested)
 - PHP-enabled web server (Nginx + PHP-FPM is recommended)
 - [Composer](https://getcomposer.org/)
 - A subdomain to allocate to GoosePlus
 - Basic terminal knowledge

## Installation
First, clone this git repository and `cd` into it:

```bash
git clone https://github.com/sbrl/GoosePlus.git
cd GoosePlus;
```

Then, install the composer dependencies:

```bash
composer install
```

GoosePlus is configured by way of a [TOML](https://toml.io/) file. You can view `settings.default.toml` in the root of this repository to get an idea as to what properties are supported:

```bash
less settings.default.toml
```

Note that you don't have to specify *every* possible setting in your custom config file - only the ones you'd like to change. By default some example !bangs are configured, but you can overwrite them with your own. Here's an example config file:

```toml
name = "Bob's Rockets Search Engine"
description = "Search for all the rocket parts you could ever need!"

[basic]

default_template = "https://seanssatellites.io/search?query={{{s}}}"

[auth]
require_secret = true

# Try this Bash one-liner to generate a test:
# dd if=/dev/urandom count=32 bs=1 2>/dev/null | base64 | tr -d '=+/'
secret = "some_super_secret_secret_here"

[[search_engine]]
name = "Bill's Boosters"
bang = "bill"
icon_url = "https://wiki.billsboosters.space/favicon.png"
url_template = "https://wiki.billsboosters.space/search.php?q={{{s}}}"

[[search_engine]]
name = "Rob's Rovers"
bang = "rob"
icon_url = "https://shop.robsrovers.net/assets/rover.svg"
url_template = "https://shop.robsrovers.net/search/?query={{{s}}}"

# Add as many other search engines as you like below
```

If it doesn't exist already, create the `data` directory, apply the appropriate permissions and create the settings file in there:

```bash
mkdir data/;
sudo touch data/settings.toml;
sudo chown -R www-data:www-data data; # Replace www-data with the username that your web server runs as
sudo chmod u=rwX,g=rwX,o=;
sudo nano data/settings.toml;
```

Finally point your PHP-enabled web server at the `src/` subdirectory, and you're away! Here's an example Nginx configuration:

```nginx
# This block is besst placed in your main nginx.conf file
upstream php {
	server unix:/run/php/php-fpm.sock;
	keepalive 8;
}

server {
	listen	80;
	listen	[::]:80;
	
	server_name search.bobsrockets.com;
	include	/etc/nginx/snippets/letsencrypt.conf;
	
	return 301 https://$host$request_uri;
}

server {
	listen	443 ssl http2;
	listen	[::]:443 ssl http2;
	
	server_name	search.bobsrockets.com;
	ssl_certificate		/etc/letsencrypt/live/search.bobsrockets.com/fullchain.pem;
	ssl_certificate_key	/etc/letsencrypt/live/search.bobsrockets.com/privkey.pem;
	
	add_header	strict-transport-security "max-age=31536000;";
	
	index	index.html index.php;
	root	/srv/GoosePlus/src;
	
	location ~* \.php$ {
		try_files	/non-existent/	@php;
	}
	
	# Just in case
	location ~* \.toml {
		deny all;
	}
	
	location @php {
		gzip_proxied		any;
		fastcgi_pass		php;
		proxy_hide_header	x-frame-options;
	}
}
```


## Contributing
Contributions are very welcome - both issues and pull requests! Please mention in your pull request that you release your work under the MPL-2.0 (see below).

If you're feeling that way inclined, the sponsor button at the top of the page (if you're on GitHub) will take you to my [Liberapay profile](https://liberapay.com/sbrl) if you'd like to donate to say an extra thank you :-)


## License
GoosePlus is released under the Mozilla Public License 2.0. The full license text is included in the `LICENSE` file in this repository. Tldr legal have a [great summary](https://tldrlegal.com/license/mozilla-public-license-2.0-(mpl-2)) of the license if you're interested.
