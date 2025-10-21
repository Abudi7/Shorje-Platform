<?php

namespace App\Controller;

use App\Service\VersionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VersionController extends AbstractController
{
    private VersionService $versionService;

    public function __construct(VersionService $versionService)
    {
        $this->versionService = $versionService;
    }

    #[Route('/version', name: 'app_version', methods: ['GET'])]
    public function version(): JsonResponse
    {
        return $this->json([
            'success' => true,
            'data' => $this->versionService->getVersionInfo(),
            'message' => 'Shorje Platform Version Information'
        ]);
    }

    #[Route('/api/version', name: 'api_version', methods: ['GET'])]
    public function apiVersion(): JsonResponse
    {
        return $this->json([
            'version' => $this->versionService->getVersion(),
            'build_date' => $this->versionService->getBuildDate(),
            'git_commit' => $this->versionService->getGitCommit(),
            'environment' => $this->versionService->isDevelopment() ? 'development' : 'production',
            'status' => 'active'
        ]);
    }

    #[Route('/health', name: 'app_health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return $this->json([
            'status' => 'healthy',
            'version' => $this->versionService->getVersion(),
            'timestamp' => date('c'),
            'uptime' => $this->getUptime(),
            'environment' => $this->versionService->isDevelopment() ? 'development' : 'production'
        ]);
    }

    private function getUptime(): string
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return sprintf('Load: %.2f, %.2f, %.2f', $load[0], $load[1], $load[2]);
        }
        return 'unknown';
    }
}
