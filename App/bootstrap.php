<?php
/**
 * Phalcon rest api bootstrap file.
 * Synchronous response
 *
 * Top & Lab v 2.0
 * Phalcon Small Service Application Template
 *
 * User: lex Gudz 27.02.18
 */

define('BASE_PATH', __DIR__ . '/../');
define('APP_PATH', BASE_PATH . '/App');


try {
    require_once BASE_PATH . '/vendor/autoload.php';

    // Using the App factory default services container
    $di = new \Phalcon\Di\FactoryDefault();

    // Add Config Service
    $di->setShared('config', function () {
        // Load Global Settings
        $confCollection = include APP_PATH . '/config/config.php';
        $config = new \Phalcon\Config($confCollection);

        return $config;
    });

    // Register Namespace Autoloader
    $loader = new \Phalcon\Loader();

    $loader->registerNamespaces([
        'App' => APP_PATH . '/',
    ])->register();

    // Read Service to App container
    include APP_PATH . '/config/service.php';

    // Create a REST application
    $app = new \Phalcon\Mvc\Micro();
    $app->setDI($di);

    // Обработка исключений
    try {
        include APP_PATH . '/router.php';

        $app->notFound(function () use ($app) {
            return $app->response->sendError('Page not found', 404);
        });

        $app->handle();

    } catch (\Exception $exc) {
        // Исключение связаное с отказом в доступе
        if ($exc instanceof \Topnlab\Common\v2\Api\Exception\ApiNotAuthorizedServiceException) {
            $app->response->sendForbidden()->send();
        } else {  // другое
            $app->response->sendError($exc->getMessage())->send();
        }
    }


} catch (Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'error'  => $e->getMessage(),
    ]);
}


