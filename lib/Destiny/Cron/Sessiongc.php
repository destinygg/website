<?php
namespace Destiny\Cron;

use Destiny\Session;
use Destiny\Logger;

class Sessiongc {

	public function execute(Logger $log) {
		Session::garbageCollect ();
	}

}