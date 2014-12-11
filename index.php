<?php
/**
 * For license information; see license.txt
 * @author frankhouweling
 * @date 11-12-14
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/vendor/autoload.php';

$app = new Silex\Application();

// Template Engine
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// SQLight Driver
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__.'/app.db',
    ),
));

// Sessions
$app->register(new Silex\Provider\SessionServiceProvider());

// URLS
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

/*
 * Application
 */
$app->get('/', function() use($app) {
    $data = file_get_contents(__DIR__ . "/gemeenten.txt");
    $gemeenten = explode("\n", $data);

    $gemeenten = array_map(function($gemeente){
        return trim($gemeente);
    }, $gemeenten);

    return $app['twig']->render('welcome.twig', array(
        'gemeenten' => $gemeenten
    ));
});

$app->post('/savecity', function() use($app){
    $app['session']->set('city', $app['request']->get('city'));
    return $app->redirect($app["url_generator"]->generate("choosecategories"));
});

$app->get('/choosecategories', function() use ($app){
    $city = $app['session']->get('city');

    return $app['twig']->render('select-categories.twig', array(
        'city' => $city
    ));

})->bind("choosecategories");

$app['debug'] = true;

$app->run();