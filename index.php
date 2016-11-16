<?php

use Slim\App;

require __DIR__ . '/vendor/autoload.php';

$settings = require __DIR__ . '/application/config.php';

global $app;
$app = new App($settings);

require __DIR__ . '/application/dependencies.php';
require __DIR__ . '/application/functions.php';
require __DIR__ . '/application/routes.php';

$app->run();
