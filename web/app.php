<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

// grant write access to group
umask(0002);

require_once __DIR__ . '/../app/AppEnvironment.php';

$environment = AppEnvironment::fromDefault();
$debug = $environment->getEnvironment() !== 'production';

if ($debug) {
    $loader = require_once __DIR__ . '/../app/autoload.php';
    Debug::enable();
} else {
    $loader = require_once __DIR__ . '/../app/bootstrap.php.cache';
}

// Enable APC for autoloading to improve performance.
// You should change the ApcClassLoader first argument to a unique prefix
// in order to prevent cache key conflicts with other applications
// also using APC.
/*
$apcLoader = new ApcClassLoader(sha1(__FILE__), $loader);
$loader->unregister();
$apcLoader->register(true);
*/

require_once __DIR__ . '/../app/AppKernel.php';
$kernel = new AppKernel($environment, $debug);
if (!$debug) {
    require_once __DIR__.'/../app/AppCache.php';
    // @see http://symfony.com/doc/current/cookbook/debugging.html
    $kernel->loadClassCache();
    $kernel = new AppCache($kernel);
}

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
