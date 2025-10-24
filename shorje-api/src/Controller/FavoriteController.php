<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductFavorite;
use App\Repository\ProductFavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FavoriteController extends AbstractController
{
    #[Route('/web/favorites/toggle', name: 'web_favorites_toggle', methods: ['POST'])]
    public function toggleFavorite(
        Request $request,
        EntityManagerInterface $em,
        ProductFavoriteRepository $favoriteRepo
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $productId = $data['productId'] ?? null;

        if (!$productId) {
            return new JsonResponse(['error' => 'معرف المنتج مطلوب'], 400);
        }

        $product = $em->getRepository(Product::class)->find($productId);
        if (!$product) {
            return new JsonResponse(['error' => 'المنتج غير موجود'], 404);
        }

        // Check if already favorited
        $favorite = $favoriteRepo->findByUserAndProduct($user, $productId);

        if ($favorite) {
            // Remove from favorites
            $em->remove($favorite);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'action' => 'removed',
                'message' => 'تم إزالة المنتج من المفضلة'
            ]);
        } else {
            // Add to favorites
            $favorite = new ProductFavorite();
            $favorite->setUser($user);
            $favorite->setProduct($product);

            $em->persist($favorite);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'action' => 'added',
                'message' => 'تم إضافة المنتج إلى المفضلة'
            ]);
        }
    }

    #[Route('/web/favorites', name: 'web_favorites_list', methods: ['GET'])]
    public function getFavorites(
        ProductFavoriteRepository $favoriteRepo
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول'], 401);
        }

        $favorites = $favoriteRepo->findByUser($user);
        
        $products = [];
        foreach ($favorites as $favorite) {
            $product = $favorite->getProduct();
            $products[] = [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'currency' => $product->getCurrency(),
                'category' => $product->getCategory(),
                'city' => $product->getCity(),
                'status' => $product->getStatus(),
                'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
                'favoritedAt' => $favorite->getCreatedAt()->format('Y-m-d H:i:s'),
                'seller' => [
                    'id' => $product->getSeller()->getId(),
                    'name' => $product->getSeller()->getFirstName() . ' ' . $product->getSeller()->getLastName(),
                ],
                'hasImages' => [
                    'image1' => $product->getImage1() !== null,
                    'image2' => $product->getImage2() !== null,
                    'image3' => $product->getImage3() !== null
                ]
            ];
        }

        return new JsonResponse([
            'success' => true,
            'products' => $products,
            'total' => count($products)
        ]);
    }

    #[Route('/web/favorites/check/{productId}', name: 'web_favorites_check', methods: ['GET'])]
    public function checkFavorite(
        int $productId,
        ProductFavoriteRepository $favoriteRepo
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['isFavorited' => false]);
        }

        $isFavorited = $favoriteRepo->isFavorited($user, $productId);

        return new JsonResponse(['isFavorited' => $isFavorited]);
    }
}

