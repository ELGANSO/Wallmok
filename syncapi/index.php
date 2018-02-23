<?php

http_response_code(500);

set_include_path('..:' . get_include_path());

use Ontic\SyncApi\IController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

require_once __DIR__ . '/vendor/autoload.php';

define('_BASE_DIR_', __DIR__);

$routes = new RouteCollection();
$controllers = [];

// Rutas
addRoute('get_unsynchronized_customers', '/customers/unsynchronized', 'UnsynchronizedCustomers', 'GET');
addRoute('mark_customer_as_synchronized', '/customers/{customerId}/synchronized', 'MarkCustomerAsSynchronized', 'PUT');
addRoute('get_unsynchronized_orders', '/orders/unsynchronized', 'UnsynchronizedOrders', 'GET');
addRoute('mark_order_as_synchronized', '/orders/{orderId}/synchronized', 'MarkOrderAsSynchronized', 'PUT');
addRoute('create_product_update_request', '/productupdates', 'CreateProductUpdateRequest', 'POST');
addRoute('get_product_update_request', '/productupdates/{requestId}', 'GetProductUpdateRequest', 'GET');
addRoute('set_order_state', '/orders/setstate', 'SetOrderState', 'POST');

$request = Request::createFromGlobals();
$context = new RequestContext();
$context->fromRequest($request);
$matcher = new UrlMatcher($routes, $context);
$parameters = $matcher->matchRequest($request);

require_once __DIR__ . '/../app/Mage.php';
Mage::app('admin');


$route = $parameters['_route'];

$routeObject = $routes->get($route);
if($routeObject === null)
{
    http_response_code(404);
    echo '404 Not Found';
    die;
}

$controllerClass = 'Ontic\SyncApi\Controllers\\' . $controllers[$route] . 'Controller';
/** @var IController $controller */
$controller = new $controllerClass();
$controller->setRequest($request);
$controller->setParameters($parameters);
$response = $controller->defaultAction();
$response->send();
die;

function addRoute($name, $path, $controllerClass, $methods = null)
{
    global $routes, $controllers;

    if($methods === null)
    {
        $methods = 'GET';
    }

    $route = new Route($path);
    $route->setMethods($methods);
    $routes->add($name, $route);
    $controllers[$name] = $controllerClass;
}
