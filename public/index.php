<?php

declare(strict_types=1);

use App\Core\App;

require dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/config.php';

$app = new App($config);
$app->run();
