<?php

require_once __DIR__ . '/../vendor/autoload.php';

$router = new Framework\Routing\Router();

// we expect the routes file to return a callable
// or else this code would break

$routes = require_once __DIR__ . '/../app/routes.php';
$routes($router);

print $router->dispatch();