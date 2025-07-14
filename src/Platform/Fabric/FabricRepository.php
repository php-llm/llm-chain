<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Fabric;

use PhpLlm\LlmChain\Platform\Fabric\Exception\PatternNotFoundException;

/**
 * Repository for loading Fabric patterns.
 */
class FabricRepository
{
    private readonly string $patternsPath;

    /**
     * @var array<string, FabricPromptInterface>
     */
    private array $cache = [];

    public function __construct(?string $patternsPath = null)
    {
        if (null === $patternsPath) {
            // Check if Fabric patterns package is installed
            if (!is_dir(\dirname(__DIR__, 2).'/vendor/php-llm/fabric-pattern')) {
                throw new \RuntimeException('Fabric patterns not found. Please install the "php-llm/fabric-pattern" package: composer require php-llm/fabric-pattern');
            }
            
            $fabricPatternPath = \dirname(__DIR__, 4).'/fabric-pattern/patterns';
            if (is_dir($fabricPatternPath)) {
                $this->patternsPath = $fabricPatternPath;
            } else {
                throw new \RuntimeException('Fabric patterns directory not found at expected location');
            }
        } else {
            $this->patternsPath = $patternsPath;
        }
    }

    /**
     * Load a Fabric pattern by name.
     *
     * @throws PatternNotFoundException
     */
    public function load(string $pattern): FabricPromptInterface
    {
        if (isset($this->cache[$pattern])) {
            return $this->cache[$pattern];
        }

        $patternPath = $this->patternsPath.'/'.$pattern;
        $systemFile = $patternPath.'/system.md';

        if (!is_dir($patternPath) || !is_file($systemFile)) {
            throw new PatternNotFoundException(\sprintf('Pattern "%s" not found at path "%s"', $pattern, $patternPath));
        }

        $content = file_get_contents($systemFile);
        if (false === $content) {
            throw new PatternNotFoundException(\sprintf('Could not read system.md for pattern "%s"', $pattern));
        }

        $metadata = $this->loadMetadata($patternPath);

        return $this->cache[$pattern] = new FabricPrompt($pattern, $content, $metadata);
    }

    /**
     * List all available patterns.
     *
     * @return string[]
     */
    public function listPatterns(): array
    {
        if (!is_dir($this->patternsPath)) {
            return [];
        }

        $patterns = [];
        $directories = scandir($this->patternsPath);

        if (false === $directories) {
            return [];
        }

        foreach ($directories as $dir) {
            if ('.' === $dir || '..' === $dir) {
                continue;
            }

            $systemFile = $this->patternsPath.'/'.$dir.'/system.md';
            if (is_dir($this->patternsPath.'/'.$dir) && is_file($systemFile)) {
                $patterns[] = $dir;
            }
        }

        sort($patterns);

        return $patterns;
    }

    /**
     * Check if a pattern exists.
     */
    public function exists(string $pattern): bool
    {
        $patternPath = $this->patternsPath.'/'.$pattern;
        $systemFile = $patternPath.'/system.md';

        return is_dir($patternPath) && is_file($systemFile);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadMetadata(string $patternPath): array
    {
        $metadata = [];

        // Check for README.md
        $readmeFile = $patternPath.'/README.md';
        if (is_file($readmeFile)) {
            $metadata['readme'] = file_get_contents($readmeFile) ?: '';
        }

        // Check for other metadata files
        $metadataFile = $patternPath.'/metadata.json';
        if (is_file($metadataFile)) {
            $jsonContent = file_get_contents($metadataFile);
            if (false !== $jsonContent) {
                $decoded = json_decode($jsonContent, true);
                if (\is_array($decoded)) {
                    $metadata = array_merge($metadata, $decoded);
                }
            }
        }

        return $metadata;
    }
}
