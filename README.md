*********************HEY FOLKS , WELCOME HERE********************* 

# Destiny.gg
Source code for the website [www.destiny.gg](http://www.destiny.gg/)
This is a work in progress!

## License

See [LICENSE.md](LICENSE.md)

## Requirements

### Building

[nodejs](http://nodejs.org/) Dependency manager

[webpack](https://webpack.github.io/) Project builder

[composer](http://getcomposer.org/) PHP dependency manager

[glue](http://glue.readthedocs.org/) Glue is a simple command line tool to generate CSS sprites

### Running

[nginx](http://httpd.apache.org/), [php 7.1+](http://php.net/), [mysql 5](http://dev.mysql.com/), [Redis](http://redis.io/download)


## Getting Started


Create the configuration file "config/config.local.php" and override what you need.

Create and load the database using `destiny.gg.sql`


### Dependencies

Then download and install [glue](http://glue.readthedocs.org/) and [composer](http://getcomposer.org/).

#### Install the node dependencies

```shell
npm install webpack -g
npm ci
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
