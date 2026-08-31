<?php
$requestedMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestPath = $_SERVER['REQUEST_URI'] ?? '/';

function redirectForeverTo($path)
{
    header("Location: {$path}", $replace = true, $code = 301);
    exit;
}

//if ($requestedMethod === 'GET' && $requestPath === '/') {
//    print <<<HTML
//        <!DOCTYPE html>
//        <html lang="en">
//        <body>
//        hello world
//        </body>
//        </html>
//HTML;
//} else if ($requestPath === '/old-home') {
//    redirectForeverTo('/');
//} else {
//    include(__DIR__ . '/includes/404.php');
//}

$routes = [
    'GET' => [
        '/' => fn() => print
            <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <body>
            hello world
            </body>
            </html>
        HTML,
        'old-home' => fn() => redirectForeverTo('/')
    ],
    'POST' => [],
    'PATCH' => [],
    'PUT' => [],
    'DELETE' => [],
    'HEAD' => [],
    '404' => fn() => include(__DIR__ . '/includes/404.php'),
    '400' => fn() => include(__DIR__ . '/includes/400.php'),
];

// this combines all the paths (for all request methods)
// into a single array, so we can quickly see if a path
// exists in any of them
$paths = array_merge(
    array_keys($routes['GET']),
    array_keys($routes['POST']),
    array_keys($routes['PATCH']),
    array_keys($routes['PUT']),
    array_keys($routes['DELETE']),
    array_keys($routes['HEAD']),
);

function abort($code)
{
    global $routes;
    header('HTTP/1.1 500 Internal Server Error');
    $routes[$code]();
}

set_error_handler(function() {
   abort(500);
});

set_exception_handler(function() {
    abort(500);
});

if (isset($routes[$requestedMethod],
    $routes[$requestedMethod][$requestPath]))
{
    $routes[$requestedMethod][$requestPath]();
} elseif (in_array($requestPath, $paths)) {
    // the path is defined, but not for this request method;
    // so we can show a 400 error (which means "bad request")
    abort(400);
} else {
    // the path isn't allowed for any request method
    // which probably means they tried a url that the application doesn't support
    abort(404);
}
