<?php
use Destiny\Common\Service\Fantasy\TeamService;

use Destiny\Common\Application;
use Destiny\Common\UserRole;
use Destiny\Common\AppEvent;
use Destiny\Common\Service\UserService;
use Destiny\Common\Service\AuthenticationService;
use Destiny\Common\AppException;
use Destiny\Common\Utils\Http;
use Destiny\Common\SessionCredentials;
use Destiny\Common\SessionCookie;
use Destiny\Common\SessionInstance;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Service\ChatIntegrationService;
use Destiny\Common\Router;
use Destiny\Common\Annotation\Handler\RouteAnnotationHandler;
use Destiny\Common\Utils\DirectoryClassIterator;
use Destiny\Common\Service\RememberMeService;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

ini_set ( 'session.gc_maxlifetime', 5 * 60 * 60 );

$context = new stdClass ();
$context->log = 'web';
require __DIR__ . '/../lib/boot.php';
$app = Application::instance ();
$app->setRouter ( new Router () );
$app->setAnnotationReader ( new FileCacheReader ( new AnnotationReader (), realpath ( Config::$a ['cache'] ['path'] ) . '/annotation/' ) );

// Setup user session
$session = new SessionInstance ();
$session->setSessionCookie ( new SessionCookie ( Config::$a ['cookie'] ) );
$session->setCredentials ( new SessionCredentials () );
$app->setSession ( $session );

// Start the session if a valid session cookie is found
Session::start ( Session::START_IFCOOKIE );

// Startup the remember me and auth service
AuthenticationService::instance ()->init ();
RememberMeService::instance ()->init ();

// Annotation reader and routing
RouteAnnotationHandler::loadClasses ( new DirectoryClassIterator ( _LIBDIR . '/', 'Destiny/Action/' ), $app->getAnnotationReader () );

// @TODO find a better place for this
// If this user has no team, create a new one
$teamId = Session::get ( 'teamId' );
if (Session::hasRole ( UserRole::USER ) && empty ( $teamId )) {
	$teamService = TeamService::instance ();
	$userId = Session::getCredentials ()->getUserId ();
	$team = $teamService->getTeamByUserId ( $userId );
	if (empty ( $team )) {
		$team = array ();
		$team ['teamId'] = $teamService->addTeam ( $userId, Config::$a ['fantasy'] ['team'] ['startCredit'], Config::$a ['fantasy'] ['team'] ['startTransfers'] );
	}
	Session::set ( 'teamId', $team ['teamId'] );
}

// Attempts to find a route and execute it
$app->executeRequest ( (isset ( $_SERVER ['REQUEST_URI'] )) ? $_SERVER ['REQUEST_URI'] : '', $_SERVER ['REQUEST_METHOD'] );
?>