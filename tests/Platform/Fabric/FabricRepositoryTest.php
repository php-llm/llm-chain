<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Fabric;

use PhpLlm\LlmChain\Platform\Fabric\Exception\PatternNotFoundException;
use PhpLlm\LlmChain\Platform\Fabric\FabricPrompt;
use PhpLlm\LlmChain\Platform\Fabric\FabricRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FabricRepository::class)]
#[Small]
final class FabricRepositoryTest extends TestCase
{
    private string $testPatternsPath;

    protected function setUp(): void
    {
        $this->testPatternsPath = sys_get_temp_dir().'/fabric-test-'.uniqid();
        mkdir($this->testPatternsPath);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->testPatternsPath);
    }

    #[Test]
    public function constructorThrowsExceptionWhenPackageNotInstalled(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Fabric patterns not found. Please install the "php-llm/fabric-pattern" package');

        new FabricRepository();
    }

    #[Test]
    public function loadExistingPattern(): void
    {
        $this->createTestPattern('test_pattern', '# Test Pattern Content');

        $repository = new FabricRepository($this->testPatternsPath);
        $prompt = $repository->load('test_pattern');

        self::assertInstanceOf(FabricPrompt::class, $prompt);
        self::assertSame('test_pattern', $prompt->getPattern());
        self::assertSame('# Test Pattern Content', $prompt->getContent());
        self::assertSame([], $prompt->getMetadata());
    }

    #[Test]
    public function loadPatternWithMetadata(): void
    {
        $this->createTestPattern('test_pattern', '# Test Pattern Content');
        file_put_contents(
            $this->testPatternsPath.'/test_pattern/README.md',
            'This is a readme'
        );
        file_put_contents(
            $this->testPatternsPath.'/test_pattern/metadata.json',
            json_encode(['author' => 'Test Author', 'version' => '1.0'])
        );

        $repository = new FabricRepository($this->testPatternsPath);
        $prompt = $repository->load('test_pattern');

        $metadata = $prompt->getMetadata();
        self::assertSame('This is a readme', $metadata['readme']);
        self::assertSame('Test Author', $metadata['author']);
        self::assertSame('1.0', $metadata['version']);
    }

    #[Test]
    public function loadNonExistingPattern(): void
    {
        $repository = new FabricRepository($this->testPatternsPath);

        $this->expectException(PatternNotFoundException::class);
        $this->expectExceptionMessage('Pattern "non_existing" not found');

        $repository->load('non_existing');
    }

    #[Test]
    public function loadUsesCache(): void
    {
        $this->createTestPattern('cached_pattern', 'Original content');

        $repository = new FabricRepository($this->testPatternsPath);
        $prompt1 = $repository->load('cached_pattern');

        // Change the file content
        file_put_contents(
            $this->testPatternsPath.'/cached_pattern/system.md',
            'Changed content'
        );

        // Should still return cached version
        $prompt2 = $repository->load('cached_pattern');
        self::assertSame($prompt1, $prompt2);
        self::assertSame('Original content', $prompt2->getContent());
    }

    #[Test]
    public function listPatterns(): void
    {
        $this->createTestPattern('pattern_a', 'Content A');
        $this->createTestPattern('pattern_b', 'Content B');
        $this->createTestPattern('pattern_c', 'Content C');
        mkdir($this->testPatternsPath.'/invalid_pattern'); // No system.md

        $repository = new FabricRepository($this->testPatternsPath);
        $patterns = $repository->listPatterns();

        self::assertSame(['pattern_a', 'pattern_b', 'pattern_c'], $patterns);
    }

    #[Test]
    public function listPatternsEmptyDirectory(): void
    {
        $repository = new FabricRepository($this->testPatternsPath);
        $patterns = $repository->listPatterns();

        self::assertSame([], $patterns);
    }

    #[Test]
    public function listPatternsNonExistingDirectory(): void
    {
        $repository = new FabricRepository('/non/existing/path');
        $patterns = $repository->listPatterns();

        self::assertSame([], $patterns);
    }

    #[Test]
    public function exists(): void
    {
        $this->createTestPattern('existing_pattern', 'Content');

        $repository = new FabricRepository($this->testPatternsPath);

        self::assertTrue($repository->exists('existing_pattern'));
        self::assertFalse($repository->exists('non_existing'));
    }

    private function createTestPattern(string $pattern, string $content): void
    {
        $patternPath = $this->testPatternsPath.'/'.$pattern;
        mkdir($patternPath, 0777, true);
        file_put_contents($patternPath.'/system.md', $content);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir) ?: [], ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.'/'.$file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
