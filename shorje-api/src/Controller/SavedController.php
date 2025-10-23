<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SavedController extends AbstractController
{
    #[Route('/saved', name: 'app_saved')]
    public function index(): Response
    {
        return $this->render('saved/index.html.twig');
    }
}
