<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Entity\FAQ;
use App\Entity\HelpCenter;
use App\Entity\TermsAndConditions;
use App\Repository\BlogPostRepository;
use App\Repository\FAQRepository;
use App\Repository\HelpCenterRepository;
use App\Repository\TermsAndConditionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private BlogPostRepository $blogPostRepository,
        private FAQRepository $faqRepository,
        private HelpCenterRepository $helpCenterRepository,
        private TermsAndConditionsRepository $termsRepository
    ) {}

    #[Route('/api/blog', name: 'api_blog_list', methods: ['GET'])]
    public function getBlogPosts(Request $request): JsonResponse
    {
        $category = $request->query->get('category');
        $limit = (int) $request->query->get('limit', 10);
        $offset = (int) $request->query->get('offset', 0);

        if ($category) {
            $posts = $this->blogPostRepository->findByCategory($category);
        } else {
            $posts = $this->blogPostRepository->findPublished();
        }

        $posts = array_slice($posts, $offset, $limit);

        $formattedPosts = array_map(function($post) {
            return [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'excerpt' => $post->getExcerpt(),
                'content' => $post->getContent(),
                'featuredImage' => $post->getFeaturedImage(),
                'slug' => $post->getSlug(),
                'viewCount' => $post->getViewCount(),
                'author' => [
                    'id' => $post->getAuthor()->getId(),
                    'name' => $post->getAuthor()->getFullName(),
                    'avatar' => $post->getAuthor()->getAvatarUrl()
                ],
                'createdAt' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
                'publishedAt' => $post->getPublishedAt()?->format('Y-m-d H:i:s')
            ];
        }, $posts);

        return $this->json([
            'posts' => $formattedPosts,
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset,
                'total' => count($formattedPosts)
            ]
        ]);
    }

    #[Route('/api/blog/{id}', name: 'api_blog_show', methods: ['GET'])]
    public function getBlogPost(int $id): JsonResponse
    {
        $post = $this->blogPostRepository->find($id);
        
        if (!$post || !$post->isPublished()) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        // Increment view count
        $post->incrementViewCount();
        $this->em->flush();

        return $this->json([
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'excerpt' => $post->getExcerpt(),
            'content' => $post->getContent(),
            'featuredImage' => $post->getFeaturedImage(),
            'slug' => $post->getSlug(),
            'viewCount' => $post->getViewCount(),
            'author' => [
                'id' => $post->getAuthor()->getId(),
                'name' => $post->getAuthor()->getFullName(),
                'avatar' => $post->getAuthor()->getAvatarUrl()
            ],
            'createdAt' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
            'publishedAt' => $post->getPublishedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/api/faq', name: 'api_faq_list', methods: ['GET'])]
    public function getFAQs(Request $request): JsonResponse
    {
        $category = $request->query->get('category');
        $search = $request->query->get('search');

        if ($search) {
            $faqs = $this->faqRepository->search($search);
        } elseif ($category) {
            $faqs = $this->faqRepository->findByCategory($category);
        } else {
            $faqs = $this->faqRepository->findActive();
        }

        $formattedFAQs = array_map(function($faq) {
            return [
                'id' => $faq->getId(),
                'question' => $faq->getQuestion(),
                'answer' => $faq->getAnswer(),
                'category' => $faq->getCategory(),
                'viewCount' => $faq->getViewCount(),
                'createdAt' => $faq->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }, $faqs);

        return $this->json(['faqs' => $formattedFAQs]);
    }

    #[Route('/api/faq/{id}', name: 'api_faq_show', methods: ['GET'])]
    public function getFAQ(int $id): JsonResponse
    {
        $faq = $this->faqRepository->find($id);
        
        if (!$faq || !$faq->isActive()) {
            return $this->json(['error' => 'FAQ not found'], 404);
        }

        // Increment view count
        $faq->incrementViewCount();
        $this->em->flush();

        return $this->json([
            'id' => $faq->getId(),
            'question' => $faq->getQuestion(),
            'answer' => $faq->getAnswer(),
            'category' => $faq->getCategory(),
            'viewCount' => $faq->getViewCount(),
            'createdAt' => $faq->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/api/help', name: 'api_help_list', methods: ['GET'])]
    public function getHelpCenter(Request $request): JsonResponse
    {
        $category = $request->query->get('category');
        $search = $request->query->get('search');

        if ($search) {
            $helpItems = $this->helpCenterRepository->search($search);
        } elseif ($category) {
            $helpItems = $this->helpCenterRepository->findByCategory($category);
        } else {
            $helpItems = $this->helpCenterRepository->findActive();
        }

        $formattedHelp = array_map(function($help) {
            return [
                'id' => $help->getId(),
                'title' => $help->getTitle(),
                'content' => $help->getContent(),
                'category' => $help->getCategory(),
                'icon' => $help->getIcon(),
                'viewCount' => $help->getViewCount(),
                'createdAt' => $help->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }, $helpItems);

        return $this->json(['help' => $formattedHelp]);
    }

    #[Route('/api/help/{id}', name: 'api_help_show', methods: ['GET'])]
    public function getHelpItem(int $id): JsonResponse
    {
        $help = $this->helpCenterRepository->find($id);
        
        if (!$help || !$help->isActive()) {
            return $this->json(['error' => 'Help item not found'], 404);
        }

        // Increment view count
        $help->incrementViewCount();
        $this->em->flush();

        return $this->json([
            'id' => $help->getId(),
            'title' => $help->getTitle(),
            'content' => $help->getContent(),
            'category' => $help->getCategory(),
            'icon' => $help->getIcon(),
            'viewCount' => $help->getViewCount(),
            'createdAt' => $help->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/api/terms', name: 'api_terms_current', methods: ['GET'])]
    public function getCurrentTerms(): JsonResponse
    {
        $terms = $this->termsRepository->findCurrent();
        
        if (!$terms) {
            return $this->json(['error' => 'Terms and conditions not found'], 404);
        }

        return $this->json([
            'id' => $terms->getId(),
            'title' => $terms->getTitle(),
            'content' => $terms->getContent(),
            'version' => $terms->getVersion(),
            'effectiveDate' => $terms->getEffectiveDate()?->format('Y-m-d H:i:s'),
            'createdAt' => $terms->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/api/terms/history', name: 'api_terms_history', methods: ['GET'])]
    public function getTermsHistory(): JsonResponse
    {
        $terms = $this->termsRepository->findActive();

        $formattedTerms = array_map(function($term) {
            return [
                'id' => $term->getId(),
                'title' => $term->getTitle(),
                'version' => $term->getVersion(),
                'isCurrent' => $term->isCurrent(),
                'effectiveDate' => $term->getEffectiveDate()?->format('Y-m-d H:i:s'),
                'createdAt' => $term->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }, $terms);

        return $this->json(['terms' => $formattedTerms]);
    }
}
