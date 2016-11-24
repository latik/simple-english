<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use Latik\Worksheet;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

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
        ->add('transcription')
        ->add('image')
        ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $post = $form->getData();
        try {
            $worksheet = $app['google_worksheet'];
            $worksheet->editCell(1, 4, 'time');
            $i = 1;
            foreach (array_keys($post) as $key) {
                $worksheet->editCell(1, $i, $key);
                $i++;
            }
            $row = array_merge($post, ['time' => \Carbon\Carbon::now()]);
            $worksheet->insertRow($row);

            return $app->redirect('/');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    return $app['twig']->render('form.twig', ['form' => $form->createView()]);
});

$app->get('/settings', function () use ($app) {
    return $app['twig']->render('settings.twig');
});
//----------------------------------

$app->run();
