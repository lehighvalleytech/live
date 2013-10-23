<?php
use Phlyty\App;
use Zend\Mvc\Router\Http\Segment as SegmentRoute;
require_once '../vendor/autoload.php';
ini_set('date.timezone', 'UTC');
$app = new App();


$cache = Zend\Cache\StorageFactory::factory(array(
    'adapter' => array(
        'name'    => 'apc',
        'options' => array('ttl' => 3600),
    ),
    'plugins' => array(
        'exception_handler' => array('throw_exceptions' => false),
    ),
));

/**
 * Podcast Feeds
 */

$route = SegmentRoute::factory(array(
    'route' => '/feed/:name[.:format]',
    'constraints' => array(
        'name'   => '(lvtech|developers)',
        'format' => '(rss|atom|json)',
    ),
    'defaults' => array(
        'format' => 'atom',
    ),
));

$app->get($route, function (App $app) use ($cache) {
    $name   = $app->params()->getParam('name');
    $format = $app->params()->getParam('format');

    //TODO: move to config
    switch($name){
        case 'lvtech':
            $playlist = '8260212';
            $class = '\LVTech\Radio\Feed\LVTech';
            break;
        case 'developers';
            $playlist = '3894559';
            $class = '\LVTech\Radio\Feed\Developers';
            break;
        default:
            $app->halt('invalid feed');
    }

    $soundcloud = new LVTech\Services\Soundcloud\CachedClient(getenv('SOUNDCLOUD_KEY'), LVTech\Services\Soundcloud\Client::API, $cache);
    $playlist = $soundcloud->getPlaylist($playlist);

    switch($format){
        case 'rss':
        case 'atom':
            $app->response()->getHeaders()->addHeaderLine('Content-Type', 'application/' . $format . '+xml');
            $feed = new $class($playlist, $format);
            $app->response()->setContent($feed->getFeed());
            break;
        case 'json':
            $app->response()->getHeaders()->addHeaderLine('Content-Type', 'application/json');
            $feed = new $class($playlist); //TODO: odd usage to pull the rss data and push into json, feels wrong
            $feed = $feed->jsonSerialize();

            foreach($feed['tracks'] as $index => $data){
                $oembed = $soundcloud->getOembed($data['url']);
                $feed['tracks'][$index]['embed'] = $oembed->get('html');
            }

            $app->response()->setContent(json_encode($feed));
            break;
    }

})->name('feed');

/**
 * Live Stream / Status
 */
$route = SegmentRoute::factory(array(
    'route' => '/live[.:format]',
    'constraints' => array(
        'format' => '(json|)',
    ),
    'defaults' => array(
        'format' => '',
    ),
));

$app->get($route, function (App $app) {
    $format = $app->params()->getParam('format');
    $stream = new LVTech\Radio\Stream();

    //request for stream status
    if('json' == $format){
        $app->response()->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $app->response()->setContent(json_encode(array('online' => $stream->isOnline())));
        return;
    }

    //request for live stream, try to give the device what it wants
    $agent = $app->request()->getHeaders()->get('User-Agent');

    if(strpos($agent->getFieldValue(), 'Android')){
        $app->redirect($stream->getMP3());
    } else {
        $app->redirect($stream->getM3u());
    }

})->name('live');

/**
 * Main Site
 */
$app->get('/', function (App $app) {
   $app->request()->setContent(include('index.html'));
});

$app->events()->attach('500', function (\Zend\EventManager\Event $e) {
    /* @var $app App */
    $app = $e->getTarget();
    $exception = $e->getParam('exception');

    error_log($exception);

    //check if it's an http exception
    try{
        $app->response()->setStatusCode($exception->getCode());
    } catch (Exception $e) {
        $app->response()->setStatusCode(500);
    }

    $app->response()->setContent($exception->getMessage());
});

$app->run();
