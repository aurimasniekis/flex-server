<?php

use AurimasNiekis\FlexServer\WebApplication;
use Thruster\Component\HttpMessage\ServerRequest;
use Thruster\Component\HttpResponse\ResponseSender;

require_once  __DIR__ . '/../vendor/autoload.php';

$app = new WebApplication();
$response = $app->processRequest(ServerRequest::fromGlobals());

ResponseSender::send($response);