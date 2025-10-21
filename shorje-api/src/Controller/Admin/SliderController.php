<?php

namespace App\Controller\Admin;

use App\Entity\SliderImage;
use App\Repository\SliderImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/slider')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class SliderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SliderImageRepository $sliderImageRepo
    ) {}

    #[Route('/', name: 'admin_slider_index')]
    public function index(): Response
    {
        $sliderImages = $this->sliderImageRepo->findBy([], ['sortOrder' => 'ASC']);

        return $this->render('admin/slider/index.html.twig', [
            'sliderImages' => $sliderImages
        ]);
    }

    #[Route('/new', name: 'admin_slider_new')]
    public function new(Request $request): Response
    {
        $sliderImage = new SliderImage();

        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $description = $request->request->get('description');
            $buttonText = $request->request->get('buttonText');
            $buttonUrl = $request->request->get('buttonUrl');
            $buttonText2 = $request->request->get('buttonText2');
            $buttonUrl2 = $request->request->get('buttonUrl2');
            $sortOrder = (int) $request->request->get('sortOrder', 0);
            $isActive = $request->request->get('isActive') === 'on';

            $sliderImage->setTitle($title);
            $sliderImage->setDescription($description);
            $sliderImage->setButtonText($buttonText);
            $sliderImage->setButtonUrl($buttonUrl);
            $sliderImage->setButtonText2($buttonText2);
            $sliderImage->setButtonUrl2($buttonUrl2);
            $sliderImage->setSortOrder($sortOrder);
            $sliderImage->setIsActive($isActive);

            // Handle image upload
            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $imageData = file_get_contents($imageFile->getPathname());
                $mimeType = $imageFile->getMimeType();
                
                $sliderImage->setImage($imageData);
                $sliderImage->setImageMimeType($mimeType);
            }

            $this->em->persist($sliderImage);
            $this->em->flush();

            $this->addFlash('success', 'تم إضافة صورة الشريحة بنجاح');
            return $this->redirectToRoute('admin_slider_index');
        }

        return $this->render('admin/slider/new.html.twig', [
            'sliderImage' => $sliderImage
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_slider_edit')]
    public function edit(SliderImage $sliderImage, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $description = $request->request->get('description');
            $buttonText = $request->request->get('buttonText');
            $buttonUrl = $request->request->get('buttonUrl');
            $buttonText2 = $request->request->get('buttonText2');
            $buttonUrl2 = $request->request->get('buttonUrl2');
            $sortOrder = (int) $request->request->get('sortOrder', 0);
            $isActive = $request->request->get('isActive') === 'on';

            $sliderImage->setTitle($title);
            $sliderImage->setDescription($description);
            $sliderImage->setButtonText($buttonText);
            $sliderImage->setButtonUrl($buttonUrl);
            $sliderImage->setButtonText2($buttonText2);
            $sliderImage->setButtonUrl2($buttonUrl2);
            $sliderImage->setSortOrder($sortOrder);
            $sliderImage->setIsActive($isActive);
            $sliderImage->setUpdatedAt(new \DateTime());

            // Handle image upload
            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $imageData = file_get_contents($imageFile->getPathname());
                $mimeType = $imageFile->getMimeType();
                
                $sliderImage->setImage($imageData);
                $sliderImage->setImageMimeType($mimeType);
            }

            $this->em->flush();

            $this->addFlash('success', 'تم تحديث صورة الشريحة بنجاح');
            return $this->redirectToRoute('admin_slider_index');
        }

        return $this->render('admin/slider/edit.html.twig', [
            'sliderImage' => $sliderImage
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_slider_delete', methods: ['POST'])]
    public function delete(SliderImage $sliderImage): Response
    {
        $this->em->remove($sliderImage);
        $this->em->flush();

        $this->addFlash('success', 'تم حذف صورة الشريحة بنجاح');
        return $this->redirectToRoute('admin_slider_index');
    }

    #[Route('/{id}/toggle', name: 'admin_slider_toggle', methods: ['POST'])]
    public function toggle(SliderImage $sliderImage): JsonResponse
    {
        $sliderImage->setIsActive(!$sliderImage->isActive());
        $this->em->flush();

        return $this->json([
            'success' => true,
            'isActive' => $sliderImage->isActive()
        ]);
    }

    #[Route('/{id}/image', name: 'admin_slider_image')]
    public function getImage(SliderImage $sliderImage): Response
    {
        if (!$sliderImage->getImage()) {
            throw $this->createNotFoundException('الصورة غير موجودة');
        }

        $response = new Response(stream_get_contents($sliderImage->getImage()));
        $response->headers->set('Content-Type', $sliderImage->getImageMimeType());
        $response->headers->set('Content-Disposition', 'inline');

        return $response;
    }
}
