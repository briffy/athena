<?php
// This controls all the routes available to the application.  Don't leave trailing slashes.  You can forward a route to a template file or a controller.
// The format to add a new route is:  $routes->new("/",   "GET",    "index",    0);
//                                                 URL    Method   Destination  Auth
// URL = The path after the domain.  No trailing slashes.
// Method = Whether it's a GET/POST/PUT method.  Useful for differentiating between showing a form or processing a postback for a form.
// Destination = Where to route the request to.  This can either be to a template file by just specifying the name of the file without the extension (please make sure it exists in the templates directory) or in the "controller@method" format (please create a corresponding controller in the controllers directory).
// Auth = Determines whether authentication is required to view that  route.

// This holds the routes table that the rest of the application will reference to check if a route exists.  If no route exists then a 404 is displayed.
$routes = new Routes();

$routes->new("/", "GET", "index", 0);
$routes->new("/login", "GET", "login@show", 0);
$routes->new("/login", "POST", "login@process", 0);
$routes->new("/register", "GET", "register@show", 0);
$routes->new("/register", "POST", "register@process", 0);
$routes->new("/register/{postback}", "POST", "register@process", 0);
$routes->new("/messenger", "GET", "messenger@show", 1);
$routes->new("/messenger/{username}", "GET", "messenger@show", 1);
$routes->new("/messenger/message/new/{username}", "POST", "message@new", 1);
$routes->new("/messenger/message/feed/{username}/{read}", "GET", "message@feed", 1);
$routes->new("/messenger/contact/new", "POST", "contact@new", 1);
$routes->new("/messenger/contact/process/{username}", "POST", "contact@process", 1);
?>