# Destiny.gg
Source code for the website http://www.destiny.gg/
Work in progress...

## License

The design including all CSS and images by [http://www.destiny.gg/] unless otherwise noted, is licensed under a Creative Commons Attribution-NonCommercial-NoDerivs 3.0 Unported License.
http://creativecommons.org/licenses/by-nc-nd/3.0/deed.en_US

All JavaScript, PHP and Database schemas by [http://www.destiny.gg/] unless otherwise noted, is licensed under a Creative Commons Attribution-ShareAlike 3.0 Unported License.
http://creativecommons.org/licenses/by-sa/3.0/deed.en_US

Illustration of Destiny used throughout [http://www.destiny.gg/] owned by @elevencyan

League of Legends images owned by Riot inc.


## Requirements
[composer](http://getcomposer.org/download/) is required to pull dependencies.


## Installation
Add your custom configuration file to /config/config.local.php, override what you need.

Composer setup
	>composer upgrade
	
Pack files build
	>php -f "scripts/pack.php"

Install the database structure
	destiny.gg.sql


## The cron
All api requests and heavy tasks are done on a single cron task (currently running every 60 seconds on the live server)
This is controlled by the Scheduler, by running "Tasks".

If you are running the website locally, you can call this file manually, or setup a cron.

/cron/index.php

The table "[prefix_]scheduled_tasks" will show when specific tasks have been run.

If you don't run this, you will get empty UI and limited functionality in the site.