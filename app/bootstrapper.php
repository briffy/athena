<?php
// The bootstrapper starts the session and loads some essential stuff like config files and classes
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$config = parse_ini_file('/var/www/athena.briffy.net/app/config/main.conf');
require($config['CLASSES_DIRECTORY']."routes.php");
require($config['CLASSES_DIRECTORY']."route.php");
require($config['CLASSES_DIRECTORY']."db.php");
require($config['ROOT_DIRECTORY']."routing.php");
require($config['CLASSES_DIRECTORY']."request.php");


// Loads all of the controller classes
foreach(glob("/var/www/athena.briffy.net/app/controllers/*.php") as $filename)
{
    include $filename;
}

// Generates a new page request and feeds the current request URI and method type through to the process method after doing authentication
$Page = new Request();
$Page->Process($routes->get($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'])->auth());