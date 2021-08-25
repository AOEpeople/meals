<?php

use Symfony\Component\Dotenv\Dotenv;

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT.'/vendor/autoload.php';

if (file_exists(APP_ROOT.'/config/bootstrap.php')) {
    require APP_ROOT.'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
