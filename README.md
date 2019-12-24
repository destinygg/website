# Destiny.gg
Source code for the website [www.destiny.gg](http://www.destiny.gg/)
This is a work in progress!

## License

See [LICENSE.md](LICENSE.md)

## Requirements

### Building

[docker](https://www.docker.com/) Docker

[nodejs](http://nodejs.org/) Dependency manager

[webpack](https://webpack.github.io/) Project builder

[composer](http://getcomposer.org/) PHP dependency manager

[glue](http://glue.readthedocs.org/) Glue is a simple command line tool to generate CSS sprites

### Running

[nginx](http://httpd.apache.org/), [php 7.1+](http://php.net/), [mysql 5](http://dev.mysql.com/), [Redis](http://redis.io/download)


## Getting Started

1. Create the configuration file `cp config/config.local.conf.example config/config.local.conf` and override anything you want to.
2. Install `docker-compose up -d`
3. Create and load the database using `docker-compose exec -T db mysql -uroot -pdgg dgg < destiny.gg.sql`
4. Load Seed Data `docker-compose exec -T db mysql -uroot -pdgg dgg < destiny.gg.data.sql`


### Dependencies

Then download and install [glue](http://glue.readthedocs.org/) and [composer](http://getcomposer.org/).

#### Install the node dependencies

```shell
npm install webpack -g
npm install
composer install -no-dev
```

#### You can now build the project.

```shell
npm run build
```
or
```shell
webpack -p
```

#### Building while developing

```shell
webpack -w
```
or
```shell
webpack
```

## Cron job

The retrieval of 3rd party data (e.g. twitter feed) is run through a php script that is polled at a set interval.

If you are running the website locally, you can call this file manually, or setup a cron. `./cron/index.php`

```shell
php -f ./cron/index.php
```
