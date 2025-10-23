<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SellersController extends AbstractController
{
    #[Route('/sellers', name: 'app_sellers')]
    public function index(): Response
    {
        return $this->render('sellers/index.html.twig');
    }
}
