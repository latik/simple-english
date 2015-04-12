<?php
require_once dirname(__DIR__) .'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\FormServiceProvider;
use Latik\Worksheet;

$app = new Silex\Application();

$app->register(new FormServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => dirname(__DIR__).'/views',
    'twig.form.templates' => [
        'bootstrap_3_horizontal_layout.html.twig',
    ],
]);
$app->register(new Silex\Provider\TranslationServiceProvider(), ['locale_fallbacks' => array('en')]);

\Dotenv::load(dirname(__DIR__));

$app['debug'] = getenv('APP_DEBUG') ? : false;

$app['googleWorksheetConfig'] = [
    'privateKeyPath' => dirname(__DIR__) . '/data/' . getenv('APP_KEY'),
    'serviceAccountName' => getenv('SERVICE_ACCOUNT'),
    'app_key' => getenv('APP_KEY'),
    'googleApplicationName' => getenv('GOOGLE_APP'),
    'spreadsheetTitle' => getenv('SPREADSHEET_TITLE'),
    'worksheetTitle' => getenv('WORKSHEET_TITLE')
];

$app['google_worksheet'] = $app->share(function ($app) {
    return new Worksheet($app['googleWorksheetConfig']);
});

//----------------------------------
//
//
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig');
});

$app->get('/list', function () use ($app) {
    return $app->json($app['google_worksheet']->all());
});

//
$app->get('/categories', function () use ($app) {
    return $app->json($app['google_worksheet']->categories());
});

//
$app->get('/listByCategory', function () use ($app) {
    return $app['twig']->render('lists.twig', ['worksheet' => $app['google_worksheet']->orderByCategory()]);
});

//
$app->match('/form', function (Request $request) use ($app) {
    $form = $app['form.factory']->createBuilder('form')
        ->setAction('/form')
        ->add('english')
        ->add('russian')
        ->add('category')
        ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $post = $form->getData();
        try {
            $worksheet = new Worksheet($app['googleWorksheetConfig']);
            $worksheet->cellFeed->editCell(1,4, "time");
            $i=1;
            foreach (array_keys($post) as $key) {
                $cellFeed->editCell(1,$i, $key);
                $i++;
            }
            $row = array_merge($post,['time'=>\Carbon\Carbon::now()]);
            $worksheet->listFeed->insert($row);

            return $app->redirect('/');
        } catch (Exception $e){
            die($e->getMessage());
        }
    }
    return $app['twig']->render('form.twig', ['form' => $form->createView()]);
});

$app->get('/settings', function () use ($app) {
    return $app['twig']->render('settings.twig');
});
//----------------------------------

$app->run();
