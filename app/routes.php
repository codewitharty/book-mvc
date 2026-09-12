<?php

use Framework\Routing\Router;

return function (Router $router) {
    $router->add(
        'GET', '/',
        fn() => view('products/list', ['number' => 42]),
    );

    $router->add(
        'GET', '/old-home',
        fn() => $router->redirect('/')
    );

    $router->add(
        'GET', '/has-server-error',
        fn() => throw new Exception(),
    );

    $router->add(
        'GET', '/has-validation-error',
        fn() => $router->dispatchNotAllowed(),
    );

    $router->add(
        'GET', '/products/view/{product}',
        function () use ($router) {

            $parameters = $router->current()->parameters();

            return view('products/view', ['product' => $parameters['product'], 'scary' => '<script>alert(\'boo!\')</script>']);
        }
    );

    $router->add(
        'GET', '/products/edit/{page}',
        function () use ($router) {

            $parameters = $router->current()->parameters();
            $parameters['page'] ??= 1;

            return "products for page {$parameters['page']}";
        }
    )->name('product-list');

    $router->errorHandler(404, fn() => 'Whoops! Something went wrong!');
};