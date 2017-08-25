<?php

// Configure your GeniBase for the production environment

// === DataBase ===============================================================

$app['db.options'] = array(
    'driver'    => 'pdo_mysql',
    'host'      => 'localhost',
    'user'      => 'genibase',
    'password'  => 'genibase',
    'dbname'    => 'genibase',
    'charset'   => 'utf8',
    'prefix'    => 'gb_',
    'driverOptions' => array(
        1002 => 'SET NAMES utf8'
    ),
);

// === API keys ===============================================================

$app['api_key.svrt_1914'] = '402618';

$app['api_key.google'] = 'AIzaSyChd119hmRUhPEablwFpKTm7XtKdI68EiA';
$app['api_key.google.secret'] = 'C42FnZuVGX1gcIDR3sf_R-XhtUQ=';

$app['api_key.facebook'] = '136324196981131';
$app['api_key.facebook.secret'] = 'd704ec80a9eaaca92ab12e7aeb03d6cc';







// Below this line is configs you may not want to change

// === Logging ================================================================

$app['monolog.name']    = 'GeniBase';

$app['monolog.logfile'] = "${root_dir}/var/logs/" .
    \Carbon\Carbon::now()->format('Y-m-d') . '.log';
$app['monolog.logfile.expired'] = "${root_dir}/var/logs/" .
    \Carbon\Carbon::now()->subWeeks(7)->format('Y-m-d') . '.log';

$app['monolog.level']   = \Monolog\Logger::ERROR;

// === Rate Limiting ==========================================================

$app['rate_limiter.cache_dir'] = "${root_dir}/tmp/cache/rate";
$app['rate_limiter.whitelist_dir'] = "${root_dir}/var/ip_whitelists";

// === Caching ================================================================

$app['http_cache.cache_dir'] = "${root_dir}/tmp/cache/http";

// === Twig ===================================================================

$app['twig.path'] = array(
    "${root_dir}/src/_views"
);
$app['twig.options'] = array(
    'cache' => "${root_dir}/tmp/cache/twig"
);

// === Sessions ===============================================================

$app['session.storage.save_path'] = "${root_dir}/tmp/sessions";

// === Translator =============================================================

$app['translator.resource'] = "${root_dir}/app/lang/";

// === Importer configs =======================================================

$app['svrt.1914.store'] = "${root_dir}/var/store";
$app['places.store'] = "${root_dir}/var/store";