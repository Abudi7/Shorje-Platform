<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\Follow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'products_page', methods: ['GET'])]
    public function productsPage(): Response
    {
        return $this->render('products/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/products/my', name: 'my_products_page', methods: ['GET'])]
    public function myProductsPage(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('products/my.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/product/{id}', name: 'single_product_page', methods: ['GET'])]
    public function singleProductPage(int $id, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('المنتج غير موجود');
        }

        return $this->render('products/single.html.twig', [
            'product' => $product,
            'user' => $this->getUser()
        ]);
    }

    // Add method for /products/{id} route
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('المنتج غير موجود');
        }

        return $this->render('products/single.html.twig', [
            'product' => $product,
            'user' => $this->getUser()
        ]);
    }

    // Add method for /products route
    public function index(): Response
    {
        return $this->render('products/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/api/products', name: 'api_products_list', methods: ['GET'])]
    #[Route('/web/products', name: 'web_products_list', methods: ['GET'])]
    public function listProducts(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $category = $request->query->get('category');
        $city = $request->query->get('city');
        $minPrice = $request->query->get('min_price');
        $maxPrice = $request->query->get('max_price');
        $search = $request->query->get('search');
        $dateFrom = $request->query->get('date_from');
        $sort = $request->query->get('sort', 'newest');
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 10);

        $qb = $em->createQueryBuilder()
            ->select('p', 's')
            ->from(Product::class, 'p')
            ->join('p.seller', 's')
            ->where('p.status = :status')
            ->setParameter('status', 'available');

        // Apply sorting
        switch ($sort) {
            case 'price_low':
                $qb->orderBy('p.price', 'ASC');
                break;
            case 'price_high':
                $qb->orderBy('p.price', 'DESC');
                break;
            case 'oldest':
                $qb->orderBy('p.createdAt', 'ASC');
                break;
            case 'name_asc':
                $qb->orderBy('p.title', 'ASC');
                break;
            case 'name_desc':
                $qb->orderBy('p.title', 'DESC');
                break;
            case 'newest':
            default:
                $qb->orderBy('p.createdAt', 'DESC');
                break;
        }

        if ($category) {
            $qb->andWhere('p.category = :category')
               ->setParameter('category', $category);
        }

        if ($city) {
            $qb->andWhere('p.city = :city')
               ->setParameter('city', $city);
        }

        if ($minPrice) {
            $qb->andWhere('p.price >= :minPrice')
               ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice) {
            $qb->andWhere('p.price <= :maxPrice')
               ->setParameter('maxPrice', $maxPrice);
        }

        if ($search) {
            $qb->andWhere('(p.title LIKE :search OR p.description LIKE :search)')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($dateFrom) {
            $qb->andWhere('p.createdAt >= :dateFrom')
               ->setParameter('dateFrom', new \DateTime($dateFrom));
        }

        $totalQuery = clone $qb;
        $total = $totalQuery->select('COUNT(p.id)')->getQuery()->getSingleScalarResult();

        $products = $qb->setFirstResult(($page - 1) * $limit)
                      ->setMaxResults($limit)
                      ->getQuery()
                      ->getResult();

        $productsData = [];
        foreach ($products as $product) {
            $productsData[] = [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'currency' => $product->getCurrency(),
                'currencyDisplay' => $product->getCurrencyDisplayName(),
                'category' => $product->getCategory(),
                'categoryDisplay' => $product->getCategoryDisplayName(),
                'city' => $product->getCity(),
                'location' => $product->getLocation(),
                'color' => $product->getColor(),
                'condition' => $product->getCondition(),
                'status' => $product->getStatus(),
                'statusDisplay' => $product->getStatusDisplayName(),
                'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
                'seller' => [
                    'id' => $product->getSeller()->getId(),
                    'name' => $product->getSeller()->getFullName(),
                    'email' => $product->getSeller()->getEmail()
                ],
                'hasImages' => [
                    'image1' => $product->getImage1() !== null,
                    'image2' => $product->getImage2() !== null,
                    'image3' => $product->getImage3() !== null
                ]
            ];
        }

        return new JsonResponse([
            'products' => $productsData,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    #[Route('/web/products', name: 'web_products_create', methods: ['POST'])]
    public function createProductWeb(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $data = json_decode($request->getContent(), true);

        $product = new Product();
        $product->setTitle($data['title'] ?? '');
        $product->setDescription($data['description'] ?? '');
        $product->setPrice($data['price'] ?? '0');
        $product->setCurrency($data['currency'] ?? 'IQD');
        $product->setCategory($data['category'] ?? '');
        $product->setCity($data['city'] ?? '');
        $product->setLocation($data['location'] ?? '');
        $product->setColor($data['color'] ?? null);
        $product->setCondition($data['condition'] ?? null);
        $product->setSeller($user);

        // Handle images
        if (isset($data['image1']) && $data['image1']) {
            $this->handleImageUpload($product, 'image1', $data['image1']);
        }
        if (isset($data['image2']) && $data['image2']) {
            $this->handleImageUpload($product, 'image2', $data['image2']);
        }
        if (isset($data['image3']) && $data['image3']) {
            $this->handleImageUpload($product, 'image3', $data['image3']);
        }

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['error' => implode(', ', $errorMessages)], 400);
        }

        $em->persist($product);
        $em->flush();

        // Send notifications to followers
        $this->notifyFollowers($em, $user, $product);

        return new JsonResponse([
            'message' => 'تم نشر المنتج بنجاح',
            'product' => [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'price' => $product->getPrice(),
                'category' => $product->getCategoryDisplayName()
            ]
        ], 201);
    }

    #[Route('/api/products/my', name: 'api_products_my', methods: ['GET'])]
    public function getMyProducts(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);

        $qb = $em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->where('p.seller = :seller')
            ->setParameter('seller', $user)
            ->orderBy('p.createdAt', 'DESC');

        $totalQuery = clone $qb;
        $total = $totalQuery->select('COUNT(p.id)')->getQuery()->getSingleScalarResult();

        $products = $qb->setFirstResult(($page - 1) * $limit)
                      ->setMaxResults($limit)
                      ->getQuery()
                      ->getResult();

        $productsData = [];
        foreach ($products as $product) {
            $productsData[] = [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'currency' => $product->getCurrency(),
                'category' => $product->getCategory(),
                'categoryDisplay' => $product->getCategoryDisplayName(),
                'city' => $product->getCity(),
                'location' => $product->getLocation(),
                'color' => $product->getColor(),
                'condition' => $product->getCondition(),
                'status' => $product->getStatus(),
                'statusDisplay' => $product->getStatusDisplayName(),
                'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
                'hasImages' => [
                    'image1' => $product->getImage1() !== null,
                    'image2' => $product->getImage2() !== null,
                    'image3' => $product->getImage3() !== null
                ]
            ];
        }

        return new JsonResponse([
            'products' => $productsData,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    #[Route('/web/products/my', name: 'web_products_my', methods: ['GET'])]
    public function getMyProductsWeb(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);

        $qb = $em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->where('p.seller = :seller')
            ->setParameter('seller', $user)
            ->orderBy('p.createdAt', 'DESC');

        $totalQuery = clone $qb;
        $total = $totalQuery->select('COUNT(p.id)')->getQuery()->getSingleScalarResult();

        $products = $qb->setFirstResult(($page - 1) * $limit)
                      ->setMaxResults($limit)
                      ->getQuery()
                      ->getResult();

        $productsData = [];
        foreach ($products as $product) {
            $productsData[] = [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'currency' => $product->getCurrency(),
                'category' => $product->getCategory(),
                'categoryDisplay' => $product->getCategoryDisplayName(),
                'city' => $product->getCity(),
                'location' => $product->getLocation(),
                'color' => $product->getColor(),
                'condition' => $product->getCondition(),
                'status' => $product->getStatus(),
                'statusDisplay' => $product->getStatusDisplayName(),
                'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
                'hasImages' => [
                    'image1' => $product->getImage1() !== null,
                    'image2' => $product->getImage2() !== null,
                    'image3' => $product->getImage3() !== null
                ]
            ];
        }

        return new JsonResponse([
            'products' => $productsData,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    #[Route('/web/products/{id}', name: 'web_products_delete', methods: ['DELETE'])]
    public function deleteProductWeb(
        int $id,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $product = $em->getRepository(Product::class)->find($id);
        if (!$product) {
            return new JsonResponse(['error' => 'المنتج غير موجود'], 404);
        }

        if ($product->getSeller() !== $user) {
            return new JsonResponse(['error' => 'ليس لديك صلاحية لحذف هذا المنتج'], 403);
        }

        $em->remove($product);
        $em->flush();

        return new JsonResponse(['message' => 'تم حذف المنتج بنجاح']);
    }

    #[Route('/api/products', name: 'api_products_create', methods: ['POST'])]
    public function createProduct(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $data = json_decode($request->getContent(), true);

        $product = new Product();
        $product->setTitle($data['title'] ?? '');
        $product->setDescription($data['description'] ?? '');
        $product->setPrice($data['price'] ?? '0');
        $product->setCurrency($data['currency'] ?? 'IQD');
        $product->setCategory($data['category'] ?? '');
        $product->setCity($data['city'] ?? '');
        $product->setLocation($data['location'] ?? '');
        $product->setColor($data['color'] ?? null);
        $product->setCondition($data['condition'] ?? null);
        $product->setSeller($user);

        // Handle images
        if (isset($data['image1']) && $data['image1']) {
            $this->handleImageUpload($product, 'image1', $data['image1']);
        }
        if (isset($data['image2']) && $data['image2']) {
            $this->handleImageUpload($product, 'image2', $data['image2']);
        }
        if (isset($data['image3']) && $data['image3']) {
            $this->handleImageUpload($product, 'image3', $data['image3']);
        }

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['error' => implode(', ', $errorMessages)], 400);
        }

        $em->persist($product);
        $em->flush();

        // Send notifications to followers
        $this->notifyFollowers($em, $user, $product);

        return new JsonResponse([
            'message' => 'تم نشر المنتج بنجاح',
            'product' => [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'price' => $product->getPrice(),
                'category' => $product->getCategoryDisplayName()
            ]
        ], 201);
    }

    #[Route('/api/products/{id}', name: 'api_products_update', methods: ['PUT'])]
    public function updateProduct(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $product = $em->getRepository(Product::class)->find($id);
        if (!$product) {
            return new JsonResponse(['error' => 'المنتج غير موجود'], 404);
        }

        if ($product->getSeller() !== $user) {
            return new JsonResponse(['error' => 'ليس لديك صلاحية لتعديل هذا المنتج'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $product->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }
        if (isset($data['price'])) {
            $product->setPrice($data['price']);
        }
        if (isset($data['category'])) {
            $product->setCategory($data['category']);
        }
        if (isset($data['city'])) {
            $product->setCity($data['city']);
        }
        if (isset($data['location'])) {
            $product->setLocation($data['location']);
        }
        if (isset($data['color'])) {
            $product->setColor($data['color']);
        }
        if (isset($data['condition'])) {
            $product->setCondition($data['condition']);
        }
        if (isset($data['status'])) {
            $product->setStatus($data['status']);
        }

        $product->setUpdatedAt(new \DateTime());

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['error' => implode(', ', $errorMessages)], 400);
        }

        $em->flush();

        return new JsonResponse(['message' => 'تم تحديث المنتج بنجاح']);
    }

    #[Route('/api/products/{id}', name: 'api_products_delete', methods: ['DELETE'])]
    public function deleteProduct(
        int $id,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $product = $em->getRepository(Product::class)->find($id);
        if (!$product) {
            return new JsonResponse(['error' => 'المنتج غير موجود'], 404);
        }

        if ($product->getSeller() !== $user) {
            return new JsonResponse(['error' => 'ليس لديك صلاحية لحذف هذا المنتج'], 403);
        }

        $em->remove($product);
        $em->flush();

        return new JsonResponse(['message' => 'تم حذف المنتج بنجاح']);
    }

    #[Route('/api/products/image/{productId}/{imageNumber}', name: 'api_products_image', methods: ['GET'])]
    public function getProductImage(
        int $productId,
        int $imageNumber,
        EntityManagerInterface $em
    ): Response {
        $product = $em->getRepository(Product::class)->find($productId);
        
        if (!$product) {
            return new Response('Product not found', 404);
        }

        $imageData = null;
        $mimeType = null;

        switch ($imageNumber) {
            case 1:
                $imageData = $product->getImage1();
                $mimeType = $product->getImage1MimeType();
                break;
            case 2:
                $imageData = $product->getImage2();
                $mimeType = $product->getImage2MimeType();
                break;
            case 3:
                $imageData = $product->getImage3();
                $mimeType = $product->getImage3MimeType();
                break;
            default:
                return new Response('Invalid image number', 400);
        }

        if (!$imageData) {
            return new Response('Image not found', 404);
        }

        // Convert BLOB to string if needed
        if (is_resource($imageData)) {
            $imageData = stream_get_contents($imageData);
        }

        $response = new Response($imageData);
        $response->headers->set('Content-Type', $mimeType ?: 'image/jpeg');
        $response->headers->set('Cache-Control', 'public, max-age=3600');

        return $response;
    }

    #[Route('/api/categories', name: 'api_categories_list', methods: ['GET'])]
    public function getCategories(): JsonResponse
    {
        $categories = [
            'car' => 'سيارات',
            'home_rental' => 'إيجار منازل',
            'apartment_rental' => 'إيجار شقق',
            'job' => 'وظائف',
            'laptop' => 'لابتوب',
            'electronics' => 'إلكترونيات',
            'fashion' => 'أزياء',
            'furniture' => 'أثاث',
            'books' => 'كتب',
            'sports' => 'رياضة',
            'other' => 'أخرى'
        ];

        return new JsonResponse(['categories' => $categories]);
    }

    private function handleImageUpload(Product $product, string $imageField, string $base64Data): void
    {
        if (strpos($base64Data, 'data:') === 0) {
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        }
        
        $imageBinary = base64_decode($base64Data);
        
        if ($imageBinary !== false) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $imageBinary);
            finfo_close($finfo);

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($mimeType, $allowedTypes)) {
                $product->{'set' . ucfirst($imageField)}($imageBinary);
                $product->{'set' . ucfirst($imageField) . 'MimeType'}($mimeType);
            }
        }
    }

    private function notifyFollowers(EntityManagerInterface $em, User $seller, Product $product): void
    {
        // Get all followers of the seller
        $followers = $em->getRepository(Follow::class)->findBy(['following' => $seller]);
        
        foreach ($followers as $follow) {
            $follower = $follow->getFollower();
            
            // Create notification for each follower
            $notification = new \App\Entity\Notification();
            $notification->setUser($follower);
            $notification->setSeller($seller);
            $notification->setProduct($product);
            $notification->setType('new_product');
            $notification->setTitle('منتج جديد من ' . $seller->getFullName());
            $notification->setMessage($seller->getFullName() . ' نشر منتج جديد: ' . $product->getTitle());
            
            $em->persist($notification);
        }
        
        $em->flush();
    }
}
