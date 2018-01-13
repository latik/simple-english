<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use Latik\Worksheet;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;

$app = new Silex\Application();

$app->register(new FormServiceProvider());
$app->register(new TwigServiceProvider(), [
    'twig.path'           => dirname(__DIR__).'/views',
    'twig.form.templates' => [
        'bootstrap_3_horizontal_layout.html.twig',
    ],
]);
$app->register(new TranslationServiceProvider(), ['locale_fallbacks' => ['en']]);

\Dotenv::load(dirname(__DIR__));

$app['debug'] = getenv('APP_DEBUG') ?: false;

$app['googleWorksheetConfig'] = [
    'privateKeyPath'        => dirname(__DIR__).'/data/'.getenv('APP_KEY'),
    'serviceAccountName'    => getenv('SERVICE_ACCOUNT'),
    'app_key'               => getenv('APP_KEY'),
    'googleApplicationName' => getenv('GOOGLE_APP'),
    'spreadsheetTitle'      => getenv('SPREADSHEET_TITLE'),
    'worksheetTitle'        => getenv('WORKSHEET_TITLE'),
];

$app['google_worksheet'] = $app->share(function ($app) {
    return new Worksheet($app['googleWorksheetConfig']);
});

//----------------------------------
//
//
$app->get('/', 'Latik\\DefaultController::index');
$app->get('/list', 'Latik\\DefaultController::list');
$app->get('/categories', 'Latik\\DefaultController::categories');
$app->get('/listByCategory', 'Latik\\DefaultController::listByCategory');
$app->get('/settings', 'Latik\\DefaultController::settings');
$app->match('/form', 'Latik\\DefaultController::form');

//----------------------------------

$app->run();
