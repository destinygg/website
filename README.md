# Destiny.gg
Source code for the website [www.destiny.gg](http://www.destiny.gg/)
This is a work in progress!

## License

The design including all CSS and images by [http://www.destiny.gg/] unless otherwise noted, is licensed under a Creative Commons Attribution-NonCommercial-NoDerivs 3.0 Unported License.
http://creativecommons.org/licenses/by-nc-nd/3.0/deed.en_US

All JavaScript, PHP and Database schemas by [http://www.destiny.gg/] unless otherwise noted, is licensed under a Creative Commons Attribution-ShareAlike 3.0 Unported License.
http://creativecommons.org/licenses/by-sa/3.0/deed.en_US

Illustration of Destiny used throughout [http://www.destiny.gg/] owned by @elevencyan


## Requirements


### Building

[nodejs](http://nodejs.org/) Dependency manager

[grunt 0.9+](http://gruntjs.com/) Project builder

[composer](http://getcomposer.org/) PHP dependency manager

[glue](http://glue.readthedocs.org/) Glue is a simple command line tool to generate CSS sprites


### Running

[Apache 2](http://httpd.apache.org/) or [Nginx](http://nginx.org/en/), [php 5.3](http://php.net/), [mysql 5](http://dev.mysql.com/), [Redis](http://redis.io/download)


## Getting Started


Create the configuration file "config/config.local.php" and override what you need.

Create and load the database using `destiny.gg.sql`


### Dependencies

Install the node dependencies

```shell
npm install
```

Install the PHP dependencies

```shell
composer install
```

Build the project

```shell
grunt build
```

## The cron job

All api requests and heavy tasks are done on a single cron task (currently running every 60 seconds on the live server)
This is controlled by the Scheduler, by running "Tasks".

If you are running the website locally, you can call this file manually, or setup a cron. `/cron/index.php`

The table "[prefix_]scheduled_tasks" will show when specific tasks have been run.

If you don't run this, you will get empty UI and limited functionality in the site.


## Grunt Task

Build the project

```shell
grunt
```

## Installing Glue 0.9.4

Download [glue](https://pypi.python.org/pypi/glue/0.9.4), extract the archive.

```shell
python setup.py install
``` 