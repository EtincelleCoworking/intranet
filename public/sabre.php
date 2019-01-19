<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

use
    Sabre\DAV,
//    Sabre\CalDAV,
    Sabre\CardDAV,
    Sabre\DAVACL;

// The autoloader
require __DIR__.'/../bootstrap/autoload.php';

// Now we're creating a whole bunch of objects
$rootDirectory = new DAV\FS\Directory('sabre/public');

// The server object is responsible for making sense out of the WebDAV protocol
$server = new DAV\Server($rootDirectory);

// If your server is not on your webroot, make sure the following line has the
// correct information
$server->setBaseUri('/sabre.php');

// The lock manager is reponsible for making sure users don't overwrite
// each others changes.
$lockBackend = new DAV\Locks\Backend\File('sabre/data/locks');
$lockPlugin = new DAV\Locks\Plugin($lockBackend);
$server->addPlugin($lockPlugin);

// This ensures that we get a pretty index in the browser, but it is
// optional.
$server->addPlugin(new DAV\Browser\Plugin());

// All we need to do now, is to fire up the server
$server->exec();




$pdo = new \PDO('sqlite:data/db.sqlite');
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

//Mapping PHP errors to exceptions
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");

// Files we need
//require_once 'vendor/autoload.php';

// Backends
$authBackend = new DAV\Auth\Backend\PDO($pdo);
$principalBackend = new DAVACL\PrincipalBackend\PDO($pdo);
$cardBackend = new CardDAV\Backend\PDO($pdo);

// Directory tree
$tree = array(
    new DAVACL\PrincipalCollection($principalBackend),
    new CardDAV\CalendarRoot($principalBackend, $cardBackend)
);  


// The object tree needs in turn to be passed to the server class
$server = new DAV\Server($tree);

// You are highly encouraged to set your WebDAV server base url. Without it,
// SabreDAV will guess, but the guess is not always correct. Putting the
// server on the root of the domain will improve compatibility.
$server->setBaseUri('/');

// Authentication plugin
$authPlugin = new DAV\Auth\Plugin($authBackend,'SabreDAV');
$server->addPlugin($authPlugin);

// CalDAV plugin
$caldavPlugin = new CalDAV\Plugin();
$server->addPlugin($caldavPlugin);

// CardDAV plugin
$carddavPlugin = new CardDAV\Plugin();
$server->addPlugin($carddavPlugin);

// ACL plugin
$aclPlugin = new DAVACL\Plugin();
$server->addPlugin($aclPlugin);

// Support for html frontend
$browser = new DAV\Browser\Plugin();
$server->addPlugin($browser);

// And off we go!
$server->exec();