<?php

namespace App\Service;

class VersionService
{
    private string $version;
    private string $buildDate;
    private string $gitCommit;

    public function __construct()
    {
        $this->version = $this->getVersion();
        $this->buildDate = date('Y-m-d H:i:s');
        $this->gitCommit = $this->getGitCommit();
    }

    public function getVersion(): string
    {
        $versionFile = __DIR__ . '/../../VERSION';
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }
        return '1.0.0';
    }

    public function getBuildDate(): string
    {
        return $this->buildDate;
    }

    public function getGitCommit(): string
    {
        $gitDir = __DIR__ . '/../../.git';
        if (is_dir($gitDir)) {
            $commit = trim(shell_exec('git rev-parse --short HEAD 2>/dev/null') ?? '');
            return $commit ?: 'unknown';
        }
        return 'unknown';
    }

    public function getFullVersion(): string
    {
        return sprintf(
            '%s (build: %s, commit: %s)',
            $this->version,
            $this->buildDate,
            $this->gitCommit
        );
    }

    public function getVersionInfo(): array
    {
        return [
            'version' => $this->version,
            'build_date' => $this->buildDate,
            'git_commit' => $this->gitCommit,
            'full_version' => $this->getFullVersion(),
            'environment' => $_ENV['APP_ENV'] ?? 'dev',
            'debug' => ($_ENV['APP_DEBUG'] ?? 'true') === 'true',
        ];
    }

    public function isDevelopment(): bool
    {
        return ($_ENV['APP_ENV'] ?? 'dev') === 'dev';
    }

    public function isProduction(): bool
    {
        return ($_ENV['APP_ENV'] ?? 'dev') === 'prod';
    }
}
