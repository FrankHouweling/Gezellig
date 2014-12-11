<?php
/**
 * For license information; see license.txt
 * @author frankhouweling
 * @date 11-12-14
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\CssSelector\CssSelector;

$app = new Silex\Application();

// Template Engine
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// SQLight Driver
//$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
//    'db.options' => array(
//        'driver'   => 'pdo_sqlite',
//        'path'     => __DIR__.'/app.db',
//    ),
//));

// Sessions
$app->register(new Silex\Provider\SessionServiceProvider());

// URLS
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Enable our CSS Selector lib
CssSelector::enableHtmlExtension();

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

$app->get("/play", function() use($app){
    return $app['twig']->render('play.twig', array(
        'photos' => $app['session']->get('photos'),
        'city' => $app['session']->get('city')
    ));
})->bind("playgame");

$app->post('/savepictures', function() use ($app){
    $photos = $app['request']->get('photos');
    $app['session']->set('photos', $photos);
    return $app->redirect($app["url_generator"]->generate("playgame"));
});

$app->get('/getdata/{city}', function($city) use($app){
    $city = urlencode($city); // Make names with spaces suitable to be URL arguments

    if( file_exists(__DIR__ . "/data/" . $city . ".json") ){
        $resultData = file_get_contents(__DIR__ . "/data/" . $city . ".json");
    }
    else{
        $data = file_get_contents( "http://www.geheugenvannederland.nl/?/nl/zoekresultaten/pagina/1/" .  $city . "/%28type%20any%20%22image%20Image%20StillImage%20video%20MovingImage%20audio%20Sound%20text%20Text%22%29%20and%20%28location%20%3D%20%22" .  $city . "%22%29/&colcount=0&wst=" );
        $data2 =  file_get_contents( "http://www.geheugenvannederland.nl/?/nl/zoekresultaten/pagina/2/" .  $city . "/%28type%20any%20%22image%20Image%20StillImage%20video%20MovingImage%20audio%20Sound%20text%20Text%22%29%20and%20%28location%20%3D%20%22" .  $city . "%22%29/&colcount=0&wst=" );
        $data3 =  file_get_contents( "http://www.geheugenvannederland.nl/?/nl/zoekresultaten/pagina/3/" .  $city . "/%28type%20any%20%22image%20Image%20StillImage%20video%20MovingImage%20audio%20Sound%20text%20Text%22%29%20and%20%28location%20%3D%20%22" .  $city . "%22%29/&colcount=0&wst=" );
        $data4 =  file_get_contents( "http://www.geheugenvannederland.nl/?/nl/zoekresultaten/pagina/4/" .  $city . "/%28type%20any%20%22image%20Image%20StillImage%20video%20MovingImage%20audio%20Sound%20text%20Text%22%29%20and%20%28location%20%3D%20%22" .  $city . "%22%29/&colcount=0&wst=" );


        // Clean HTML For Page 1
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $clean_html = $purifier->purify($data);
        $clean_html2 = $purifier->purify($data2);
        $clean_html3 = $purifier->purify($data3);
        $clean_html4 = $purifier->purify($data4);

        // Select images
        $simplexml = simplexml_load_string( "<html><body>" . $clean_html . $clean_html2 . $clean_html3 . $clean_html4 . "</body></html>" );
        $xpathQuery = CssSelector::toXPath(".resultslist img");
        $images = $simplexml->xpath($xpathQuery);

        // Make array
        $imgAr = array();
        foreach( $images as $img ){
            $response = array();
            foreach( $img->attributes() as $key => $val ){
                $response[$key] = (string)$val;
            }
            $imgAr[] = $response;
        }

        // Set the image size to large
        $i = 0;
        foreach( $imgAr as $img ){
            $imgAr[$i]['src'] = str_replace("&role=thumbnail", "&role=image&size=large", $img['src']);

            // Not all images have a 'large' size
            // I have no idea yet to check which sizes are available, so for now I just
            // send them all back

            $i++;
        }

        $resultData = json_encode($imgAr);
        file_put_contents(__DIR__ . "/data/" . $city . ".json", $resultData);
    }

    return $resultData;
});

$app['debug'] = true;

$app->run();