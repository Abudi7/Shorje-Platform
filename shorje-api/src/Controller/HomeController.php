<?php

namespace App\Controller;

use App\Repository\SliderImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\EntityManagerInterface;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    #[Route('/', name: 'app_home_redirect')]
    public function home(EntityManagerInterface $em, SliderImageRepository $sliderImageRepo): Response
    {
        $user = $this->getUser();
        $conversations = [];
        $recentProducts = [];
        
        if ($user) {
            // Get recent conversations for logged-in users
            $conversations = $em->getRepository(\App\Entity\Message::class)->findRecentConversations($user);
        }

        // Get recent products for all users
        $recentProducts = $em->getRepository(\App\Entity\Product::class)
            ->createQueryBuilder('p')
            ->join('p.seller', 's')
            ->where('p.status = :status')
            ->setParameter('status', 'available')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        // Get active slider images
        $sliderImages = $sliderImageRepo->findActiveSlides();

        return $this->render('home/index.html.twig', [
            'user' => $user,
            'conversations' => $conversations,
            'recentProducts' => $recentProducts,
            'sliderImages' => $sliderImages
        ]);
    }
}
