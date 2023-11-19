<?php

require '../Include/Config.php';

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\Authentication\Requests\LocalTwoFactorTokenRequest;
use ChurchCRM\Authentication\Requests\LocalUsernamePasswordRequest;
use ChurchCRM\dto\SystemConfig;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Slim\Middleware\VersionMiddleware;
use ChurchCRM\Utils\LoggerUtils;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

// This file is generated by Composer
require_once __DIR__.'/../vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath('/session');

if (SystemConfig::debugEnabled()) {
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', LoggerUtils::buildLogFilePath('slim'));
}

$errorMiddleware = $app->addErrorMiddleware(true, true, true, LoggerUtils::getSlimMVCLogger());
// Get the default error handler and register my custom error renderer.
$errorHandler = $errorMiddleware->getDefaultErrorHandler();

// Add Slim routing middleware
$app->addRoutingMiddleware();
$app->add(new VersionMiddleware());
$container = $app->getContainer();

require __DIR__.'/routes/password-reset.php';

$app->get('/begin', 'beginSession');
$app->post('/begin', 'beginSession');
$app->get('/end', 'endSession');
$app->get('/two-factor', 'processTwoFactorGet');
$app->post('/two-factor', 'processTwoFactorPost');

function processTwoFactorGet(Request $request, Response $response, array $args)
{
    $renderer = new PhpRenderer('templates/');
    $curUser = AuthenticationManager::getCurrentUser();
    $pageArgs = [
        'sRootPath' => SystemURLs::getRootPath(),
        'user'      => $curUser,
    ];

    return $renderer->render($response, 'two-factor.php', $pageArgs);
}

function processTwoFactorPost(Request $request, Response $response, array $args)
{
    $loginRequestBody = (object) $request->getParsedBody();
    $request = new LocalTwoFactorTokenRequest($loginRequestBody->TwoFACode);
    AuthenticationManager::authenticate($request);
}

function endSession(Request $request, Response $response, array $args)
{
    AuthenticationManager::endSession();
}

function beginSession(Request $request, Response $response, array $args)
{
    $pageArgs = [
        'sRootPath'            => SystemURLs::getRootPath(),
        'localAuthNextStepURL' => AuthenticationManager::getSessionBeginURL(),
        'forgotPasswordURL'    => AuthenticationManager::getForgotPasswordURL(),
    ];

    if ($request->getMethod() == 'POST') {
        $loginRequestBody = (object) $request->getParsedBody();
        $request = new LocalUsernamePasswordRequest($loginRequestBody->User, $loginRequestBody->Password);
        $authenticationResult = AuthenticationManager::authenticate($request);
        $pageArgs['sErrorText'] = $authenticationResult->message;
    }

    $renderer = new PhpRenderer('templates/');

    $pageArgs['prefilledUserName'] = '';
    // Defermine if approprirate to pre-fill the username field
    if (isset($_GET['username'])) {
        $pageArgs['prefilledUserName'] = $_GET['username'];
    } elseif (isset($_SESSION['username'])) {
        $pageArgs['prefilledUserName'] = $_SESSION['username'];
    }

    return $renderer->render($response, 'begin-session.php', $pageArgs);
}

// Run app
$app->run();
