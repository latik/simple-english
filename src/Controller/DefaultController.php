<?php

namespace App\Controller;

use App\Worksheet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function index(Request $request)
    {
        return $this->render('index.twig');
    }

    public function list(Request $request, Worksheet $worksheet)
    {
        return $this->json($worksheet->all());
    }

    public function categories(Request $request, Worksheet $worksheet)
    {
        return $this->json($worksheet->categories());
    }

    public function settings(Request $request)
    {
        return $this->render('settings.twig');
    }

    public function listByCategory(Request $request, Worksheet $worksheet)
    {
        return $this->render('lists.twig', ['worksheet' => $worksheet->orderByCategory()]);
    }

    public function form(Request $request, Worksheet $worksheet)
    {
        /** @var Form $form */
        $form = $this->createFormBuilder('form')
          ->add('english', TextType::class)
          ->add('russian', TextType::class)
          ->add('category', TextType::class)
          ->add('transcription', TextType::class)
          ->add('image', TextType::class)
          ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $worksheet->storeRow($form->getData());

                return $this->redirect('/');
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        return $this->render('form.twig', ['form' => $form->createView()]);
    }
}