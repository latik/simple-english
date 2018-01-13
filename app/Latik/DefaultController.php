<?php

namespace Latik;

use Silex\Application;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class DefaultController
{
    public function index(Request $request, Application $app)
    {
        return $app['twig']->render('index.twig');
    }

    public function list(Request $request, Application $app)
    {
        return $app->json($app['google_worksheet']->all());
    }

    public function categories(Request $request, Application $app)
    {
        return $app->json($app['google_worksheet']->categories());
    }

    public function settings(Request $request, Application $app)
    {
        return $app['twig']->render('settings.twig');
    }

    public function listByCategory(Request $request, Application $app)
    {
        return $app['twig']->render('lists.twig', ['worksheet' => $app['google_worksheet']->orderByCategory()]);
    }

    public function form(Request $request, Application $app)
    {
        /** @var Form $form */
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
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        return $app['twig']->render('form.twig', ['form' => $form->createView()]);
    }
}