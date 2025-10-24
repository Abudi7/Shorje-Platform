<?php

namespace App\Controller;

use App\Repository\ProductFavoriteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SavedController extends AbstractController
{
    #[Route('/saved', name: 'app_saved')]
    #[IsGranted('ROLE_USER')]
    public function index(ProductFavoriteRepository $favoriteRepo): Response
    {
        $user = $this->getUser();
        $favorites = $favoriteRepo->findByUser($user);

        return $this->render('saved/index.html.twig', [
            'favorites' => $favorites,
        ]);
    }
}
