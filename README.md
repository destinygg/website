# Destiny.gg
Source code for the website [www.destiny.gg](http://www.destiny.gg/)
This is a work in progress!

## License

See [LICENSE.md](LICENSE.md)

## Requirements

### Building

[nodejs](http://nodejs.org/) Dependency manager

[gulp](http://gulpjs.com/) Project builder

[composer](http://getcomposer.org/) PHP dependency manager

[glue](http://glue.readthedocs.org/) Glue is a simple command line tool to generate CSS sprites

### Running

[nginx](http://httpd.apache.org/), [php 5.5+](http://php.net/), [mysql 5](http://dev.mysql.com/), [Redis](http://redis.io/download)


## Getting Started


Create the configuration file "config/config.local.php" and override what you need.

Create and load the database using `destiny.gg.sql`


### Dependencies

Install the node dependencies

```shell
npm install
npm install gulp
```

Then download and install [glue](http://glue.readthedocs.org/).

Install the PHP dependencies

```shell
composer install
```

You can now build the project.

```shell
gulp
```

## The cron job

All api requests and heavy tasks are done on a single cron task (currently running every 60 seconds on the live server)
This is controlled by the Scheduler, by running "Tasks".

If you are running the website locally, you can call this file manually, or setup a cron. `/cron/index.php`

The table "[prefix_]scheduled_tasks" will show when specific tasks have been run.

If you don't run this, you will get empty UI and limited functionality in the site.
