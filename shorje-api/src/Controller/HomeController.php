<?php

namespace App\Controller;

use App\Repository\SliderImageRepository;
use App\Form\ContactFormType;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/help', name: 'app_help')]
    public function help(): Response
    {
        return $this->render('home/help.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, EmailService $emailService): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            try {
                // Send email to admin
                $emailService->sendContactFormEmail(
                    $data['name'],
                    $data['email'],
                    $data['subject'],
                    $data['message']
                );
                
                $this->addFlash('success', 'تم إرسال رسالتك بنجاح! سنتواصل معك خلال 24 ساعة.');
                
                // Redirect to prevent form resubmission
                return $this->redirectToRoute('app_contact');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'حدث خطأ في إرسال الرسالة. يرجى المحاولة مرة أخرى.');
            }
        }

        return $this->render('home/contact.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/terms', name: 'app_terms')]
    public function terms(): Response
    {
        return $this->render('home/terms.html.twig');
    }

    #[Route('/api/slider/image/{id}', name: 'api_slider_image', methods: ['GET'])]
    public function getSliderImage(int $id, SliderImageRepository $sliderImageRepo): Response
    {
        $sliderImage = $sliderImageRepo->find($id);
        
        if (!$sliderImage || !$sliderImage->getImage()) {
            throw $this->createNotFoundException('Slider image not found');
        }

        $imageData = $sliderImage->getImage();
        if (is_resource($imageData)) {
            $imageData = stream_get_contents($imageData);
        }

        $response = new Response($imageData);
        $response->headers->set('Content-Type', $sliderImage->getImageMimeType() ?: 'image/jpeg');
        $response->headers->set('Cache-Control', 'public, max-age=3600');
        
        return $response;
    }
}
