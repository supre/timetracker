<?php


use JDesrosiers\Silex\Provider\CorsServiceProvider;
use Silex\Application;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\HttpFoundation\Response;
use RoarProj\controllers\http\AuthController;
use RoarProj\controllers\http\EntriesController;
use RoarProj\controllers\http\UserController;
use RoarProj\controllers\middlewares\BodyParser;
use RoarProj\controllers\middlewares\exceptionHandlers\AccessDeniedExceptionHandler;
use RoarProj\controllers\middlewares\exceptionHandlers\AuthorizationExceptionHandler;
use RoarProj\controllers\middlewares\exceptionHandlers\ExceptionErrorHandler;
use RoarProj\controllers\middlewares\exceptionHandlers\HttpExceptionHandler;
use RoarProj\controllers\middlewares\exceptionHandlers\JsonApiExceptionHandler;
use RoarProj\controllers\middlewares\exceptionHandlers\ValidationErrorHandler;
use RoarProj\controllers\middlewares\JsonApiSerializer;
use RoarProj\controllers\middlewares\TokenValidator;
use RoarProj\providers\AuthProvider;
use RoarProj\providers\EntriesProvider;
use RoarProj\providers\LoggingProvider;
use RoarProj\providers\OrmProvider;
use RoarProj\providers\SerializationProvider;
use RoarProj\providers\UserProvider;

// FIXME error handler should convert to exception here. Not happening right now. Needs to properly handle exceptions
// as 500 errors..
ErrorHandler::register();

$app = new Application();

$baseUrl = "/v1";

/**
 * DI container configuration
 */

//orm
$app->register(
    new OrmProvider(
        getenv('MYSQL_HOST'),
        getenv('MYSQL_PORT'),
        getenv('MYSQL_SOCKET'),
        getenv('MYSQL_DATABASE'),
        getenv('MYSQL_USERNAME'),
        getenv('MYSQL_PASSWORD')
    )
);

$app->register(new LoggingProvider());

//HTTP Response Serialization
$app->register(new SerializationProvider($baseUrl));

//validation
$app->register(new ValidatorServiceProvider());

//CORS support
$app->register(new CorsServiceProvider(), []);
$app["cors-enabled"]($app);
$app["cors.exposeHeaders"] = "Access-Control-Allow-Headers,Access-Control-Allow-Methods,Access-Control-Allow-Origin";

$app->register(new UserProvider());
$app->register(new AuthProvider());
$app->register(new EntriesProvider());

//Middlewares
$app->before(new BodyParser());
$app->before(new TokenValidator($app['Token.factory'], $app['User.repository'], []));

$app->view(new JsonApiSerializer($app));

//Controllers
$app->mount($baseUrl, new AuthController());
$app->mount($baseUrl, new UserController());
$app->mount($baseUrl, new EntriesController());

$app->get(
    '/_ah/warmup',
    function () {
        return new Response(null, 204);
    }
);

//Error handlers

$app->error(new AuthorizationExceptionHandler($app));
$app->error(new AccessDeniedExceptionHandler($app));
$app->error(new ValidationErrorHandler($app));
$app->error(new HttpExceptionHandler($app));
$app->error(new JsonApiExceptionHandler($app));

// catch-all handler that spits back 500s
$app->error(new ExceptionErrorHandler());

return $app;
